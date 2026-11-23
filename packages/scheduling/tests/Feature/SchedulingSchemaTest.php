<?php

declare(strict_types=1);

use Cai\Identity\Models\User;
use Cai\Scheduling\Models\AttendanceRecord;
use Cai\Scheduling\Models\Enrollment;
use Cai\Scheduling\Models\Group;
use Cai\Scheduling\Models\Professional;
use Cai\Scheduling\Models\ProfessionalAssignment;
use Cai\Scheduling\Models\Service;
use Cai\Scheduling\Models\Session;
use Cai\Scheduling\Models\SessionParticipant;
use Cai\Students\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates the eleven scheduling tables from 03.03 and 03.04', function () {
    $sessionsTable = DB::getDriverName() === 'pgsql' ? 'sessions' : 'scheduling_sessions';

    foreach ([
        'services', 'professionals', 'enrollments', 'groups', 'group_memberships',
        'professional_assignments', 'availability_rules', $sessionsTable,
        'session_professionals', 'session_participants', 'attendance_records',
    ] as $table) {
        expect(Schema::hasTable($table))->toBeTrue($table);
    }
});

it('creates one session with one attendance record per participant', function () {
    $user = User::factory()->create();
    $service = Service::create(['name' => 'Fisioterapia', 'modality' => 'both']);
    $student = Student::factory()->create();
    $enrollment = Enrollment::create([
        'student_id' => $student->id,
        'service_id' => $service->id,
        'start_date' => now()->toDateString(),
    ]);
    $session = Session::create([
        'service_id' => $service->id,
        'session_type' => 'individual',
        'starts_at' => now()->addHour(),
        'ends_at' => now()->addHours(2),
    ]);

    $participant = SessionParticipant::create([
        'session_id' => $session->id,
        'student_id' => $student->id,
        'enrollment_id' => $enrollment->id,
    ]);

    expect(fn () => SessionParticipant::create([
        'session_id' => $session->id,
        'student_id' => $student->id,
    ]))->toThrow(QueryException::class);

    AttendanceRecord::create([
        'participant_id' => $participant->id,
        'status' => 'present',
        'recorded_by' => $user->id,
    ]);

    expect(fn () => AttendanceRecord::create([
        'participant_id' => $participant->id,
        'status' => 'absent',
        'recorded_by' => $user->id,
    ]))->toThrow(QueryException::class)
        ->and($session->attendanceRecords()->count())->toBe(1);
});

it('allows only one active responsible assignment per student', function () {
    $student = Student::factory()->create();
    $first = Professional::create(['display_name' => 'Profesional Uno']);
    $second = Professional::create(['display_name' => 'Profesional Dos']);

    $assignment = ProfessionalAssignment::create([
        'professional_id' => $first->id,
        'student_id' => $student->id,
        'role' => 'responsible',
        'valid_from' => now()->toDateString(),
    ]);

    expect(fn () => ProfessionalAssignment::create([
        'professional_id' => $second->id,
        'student_id' => $student->id,
        'role' => 'responsible',
        'valid_from' => now()->toDateString(),
    ]))->toThrow(QueryException::class);

    $assignment->update(['valid_until' => now()->toDateString()]);

    expect(fn () => ProfessionalAssignment::create([
        'professional_id' => $second->id,
        'student_id' => $student->id,
        'role' => 'responsible',
        'valid_from' => now()->addDay()->toDateString(),
    ]))->not->toThrow(QueryException::class);
});

it('keeps group membership history instead of deleting rows', function () {
    $service = Service::create(['name' => 'Musicoterapia', 'modality' => 'group']);
    $group = Group::create([
        'service_id' => $service->id,
        'name' => 'Grupo A',
        'capacity' => 5,
    ]);
    $student = Student::factory()->create();

    $group->memberships()->create([
        'student_id' => $student->id,
        'valid_from' => now()->subMonth()->toDateString(),
        'valid_until' => now()->toDateString(),
    ]);

    $group->memberships()->create([
        'student_id' => $student->id,
        'valid_from' => now()->addDay()->toDateString(),
    ]);

    expect($group->memberships()->count())->toBe(2)
        ->and($group->students()->count())->toBe(1);
});
