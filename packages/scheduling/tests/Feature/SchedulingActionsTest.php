<?php

declare(strict_types=1);

use Cai\Identity\Models\User;
use Cai\Scheduling\Actions\GenerateRecurringSessions;
use Cai\Scheduling\Actions\RecordAttendance;
use Cai\Scheduling\Actions\RescheduleSession;
use Cai\Scheduling\Actions\ScheduleSession;
use Cai\Scheduling\Data\GenerateRecurringSessionsData;
use Cai\Scheduling\Data\RecordAttendanceData;
use Cai\Scheduling\Data\RescheduleSessionData;
use Cai\Scheduling\Data\ScheduleSessionData;
use Cai\Scheduling\Exceptions\CapacityExceededException;
use Cai\Scheduling\Exceptions\OverlappingSessionException;
use Cai\Scheduling\Exceptions\ParticipantNotFoundException;
use Cai\Scheduling\Models\AttendanceRecord;
use Cai\Scheduling\Models\AvailabilityRule;
use Cai\Scheduling\Models\Group;
use Cai\Scheduling\Models\Professional;
use Cai\Scheduling\Models\Service;
use Cai\Scheduling\Models\Session;
use Cai\Students\Models\Student;
use Database\Seeders\IdentitySeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed(IdentitySeeder::class);
});

function schedulingService(): Service
{
    return Service::create(['name' => 'Fisioterapia', 'modality' => 'both']);
}

function schedulingProfessional(): Professional
{
    return Professional::create(['display_name' => 'Profesional Uno']);
}

function schedulingData(Service $service, Professional $professional, array $students = []): ScheduleSessionData
{
    $start = now()->addDay()->setTime(10, 0)->toImmutable();

    return new ScheduleSessionData(
        serviceId: $service->id,
        startsAt: $start,
        endsAt: $start->addHour(),
        participants: $students,
        professionals: [['id' => $professional->id, 'role' => 'responsible']],
    );
}

it('creates one session with participants and professionals in a single transaction', function () {
    $service = schedulingService();
    $professional = schedulingProfessional();
    $student = Student::factory()->create();

    $session = app(ScheduleSession::class)->execute(
        schedulingData($service, $professional, [$student->id]),
    );

    expect(Session::count())->toBe(1)
        ->and($session->participants()->count())->toBe(1)
        ->and($session->professionals()->count())->toBe(1)
        ->and($session->professionals()->first()->pivot->starts_at)->not->toBeNull();
});

it('rejects overlapping sessions for the same professional', function () {
    $service = schedulingService();
    $professional = schedulingProfessional();
    $student = Student::factory()->create();

    app(ScheduleSession::class)->execute(schedulingData($service, $professional, [$student->id]));

    $data = schedulingData($service, $professional, [$student->id]);

    expect(fn () => app(ScheduleSession::class)->execute(new ScheduleSessionData(
        serviceId: $service->id,
        startsAt: $data->startsAt->addMinutes(30),
        endsAt: $data->endsAt->addMinutes(30),
        professionals: $data->professionals,
    )))->toThrow(OverlappingSessionException::class);
});

it('rejects more participants than the group capacity', function () {
    $service = schedulingService();
    $professional = schedulingProfessional();
    $group = Group::create(['service_id' => $service->id, 'name' => 'Grupo A', 'capacity' => 1]);
    $students = [Student::factory()->create(), Student::factory()->create()];

    $data = schedulingData($service, $professional, array_map(fn ($s) => $s->id, $students));

    expect(fn () => app(ScheduleSession::class)->execute(new ScheduleSessionData(
        serviceId: $data->serviceId,
        startsAt: $data->startsAt,
        endsAt: $data->endsAt,
        participants: $data->participants,
        professionals: $data->professionals,
        groupId: $group->id,
    )))->toThrow(CapacityExceededException::class);
});

it('records attendance and audits a correction without adding rows', function () {
    $service = schedulingService();
    $professional = schedulingProfessional();
    $student = Student::factory()->create();
    $user = User::factory()->create();
    $session = app(ScheduleSession::class)->execute(
        schedulingData($service, $professional, [$student->id]),
    );

    $summary = app(RecordAttendance::class)->execute(new RecordAttendanceData(
        $session->id,
        [['participant_student_id' => $student->id, 'status' => 'present']],
        $user->id,
    ));

    app(RecordAttendance::class)->execute(new RecordAttendanceData(
        $session->id,
        [['participant_student_id' => $student->id, 'status' => 'absent']],
        $user->id,
    ));

    expect($summary->recorded)->toBe(1)
        ->and(AttendanceRecord::count())->toBe(1)
        ->and(AttendanceRecord::first()->status)->toBe('absent')
        ->and(DB::table('audit_events')->where('action', 'attendance.corrected')->count())->toBe(1);
});

it('refuses attendance for a student outside the session', function () {
    $service = schedulingService();
    $professional = schedulingProfessional();
    $student = Student::factory()->create();
    $outsider = Student::factory()->create();
    $user = User::factory()->create();
    $session = app(ScheduleSession::class)->execute(
        schedulingData($service, $professional, [$student->id]),
    );

    expect(fn () => app(RecordAttendance::class)->execute(new RecordAttendanceData(
        $session->id,
        [['participant_student_id' => $outsider->id, 'status' => 'present']],
        $user->id,
    )))->toThrow(ParticipantNotFoundException::class);
});

it('keeps the original session when rescheduling', function () {
    $service = schedulingService();
    $professional = schedulingProfessional();
    $student = Student::factory()->create();
    $original = app(ScheduleSession::class)->execute(
        schedulingData($service, $professional, [$student->id]),
    );

    $new = app(RescheduleSession::class)->execute(new RescheduleSessionData(
        sessionId: $original->id,
        newStartsAt: $original->starts_at->addDay()->toImmutable(),
        newEndsAt: $original->ends_at->addDay()->toImmutable(),
        reason: 'El profesional se reportó enfermo',
    ));

    expect(Session::count())->toBe(2)
        ->and($original->fresh()->status)->toBe('rescheduled')
        ->and($new->rescheduled_from_id)->toBe($original->id)
        ->and($new->participants()->count())->toBe(1);
});

it('generates recurring sessions idempotently', function () {
    $service = schedulingService();
    $professional = schedulingProfessional();
    $from = now()->addDay()->startOfDay()->toImmutable();

    AvailabilityRule::create([
        'professional_id' => $professional->id,
        'weekday' => $from->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
        'valid_from' => $from->toDateString(),
    ]);

    $generator = app(GenerateRecurringSessions::class);
    $data = new GenerateRecurringSessionsData($service->id, $from, $from->addDays(6));

    expect($generator->execute($data))->toBe(1)
        ->and($generator->execute($data))->toBe(0)
        ->and(Session::count())->toBe(1);
});

it('scopes sessions to the sessions where the professional is responsible or assistant', function () {
    $service = schedulingService();
    $ownProfessional = schedulingProfessional();
    $otherProfessional = Professional::create(['display_name' => 'Otro profesional']);

    $user = User::factory()->create(['password_changed_at' => now()]);
    $user->assignRole('profesional');
    $ownProfessional->update(['user_id' => $user->id]);

    $student = Student::factory()->create();
    app(ScheduleSession::class)->execute(schedulingData($service, $ownProfessional, [$student->id]));
    app(ScheduleSession::class)->execute(schedulingData($service, $otherProfessional, [$student->id]));

    expect(Session::visibleTo($user->fresh())->count())->toBe(1)
        ->and(Session::visibleTo(User::factory()->create(['password_changed_at' => now()])->assignRole('direccion')->fresh())->count())->toBe(2);
});
