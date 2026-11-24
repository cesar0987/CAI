<?php

declare(strict_types=1);

namespace Database\Seeders;

use Cai\Billing\Models\CashSession;
use Cai\Billing\Models\Charge;
use Cai\Billing\Models\FeePlan;
use Cai\Billing\Models\FinancialAdjustment;
use Cai\Billing\Models\Payment;
use Cai\Billing\Models\PaymentAllocation;
use Cai\Billing\Models\Receipt;
use Cai\Billing\Models\ReceiptSequence;
use Cai\Care\Audit\ClinicalNoteReadAudit;
use Cai\Care\Models\CareGoal;
use Cai\Care\Models\CarePlan;
use Cai\Care\Models\ClinicalNote;
use Cai\Care\Models\ClinicalNoteAccessGrant;
use Cai\Identity\Audit\Auditor;
use Cai\Identity\Models\FeatureFlag;
use Cai\Identity\Models\SecurityEvent;
use Cai\Identity\Models\Setting;
use Cai\Identity\Models\User;
use Cai\Scheduling\Actions\RecordAttendance;
use Cai\Scheduling\Actions\ScheduleSession;
use Cai\Scheduling\Data\RecordAttendanceData;
use Cai\Scheduling\Data\ScheduleSessionData;
use Cai\Scheduling\Models\AttendanceRecord;
use Cai\Scheduling\Models\AvailabilityRule;
use Cai\Scheduling\Models\Enrollment;
use Cai\Scheduling\Models\Group;
use Cai\Scheduling\Models\GroupMembership;
use Cai\Scheduling\Models\Professional;
use Cai\Scheduling\Models\ProfessionalAssignment;
use Cai\Scheduling\Models\Service;
use Cai\Scheduling\Models\Session;
use Cai\Scheduling\Models\SessionParticipant;
use Cai\Students\Models\AuthorizedPickup;
use Cai\Students\Models\FamilyAccount;
use Cai\Students\Models\FamilyAccountMember;
use Cai\Students\Models\Guardian;
use Cai\Students\Models\Student;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Datos 100 % ficticios para el demostrativo. Nunca se ejecuta fuera de local/testing.
 * Idempotente: se puede volver a correr sin duplicar registros.
 */
final class DemoFictitiousSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('DemoFictitiousSeeder solo puede ejecutarse en local o testing.');
        }

        $this->call(IdentitySeeder::class);

        $users = $this->users();
        $family = $this->family();
        [$ana, $bruno] = $this->students($family);
        $this->guardians($ana, $bruno, $users);
        $this->pickup($ana, $users['direccion']);

        [$service1, $service2, $pro1, $pro2, $group] = $this->scheduling($users, $ana, $bruno);
        $sessions = $this->sessions($users, $service1, $service2, $pro1, $pro2, $group, $ana, $bruno);
        $this->attendance($sessions, $users['profesional']);

        $finalized = $this->care($users, $pro1, $pro2, $ana);
        $this->billing($users, $family, $ana, $bruno, $service1, $service2);
        $this->identityExtras($users, $finalized);

        $this->command?->info('Demo ficticia lista: '.User::count().' usuarios, '
            .Student::count().' alumnos, '.Session::count().' sesiones, '
            .ClinicalNote::count().' notas, '.Charge::count().' cargos.');
    }

    /** @return array<string, User> */
    private function users(): array
    {
        $definitions = [
            'direccion' => ['direccion@cai.local', 'Dirección Demo'],
            'profesional' => ['profesional.demo@ejemplo.test', 'Dra. Demo Ficticia'],
            'coordinacion' => ['coordinacion.demo@ejemplo.test', 'Coordinación Demo'],
            'finanzas' => ['finanzas.demo@ejemplo.test', 'Finanzas Demo'],
            'secretaria' => ['secretaria.demo@ejemplo.test', 'Secretaría Demo'],
            'admin_tecnico' => ['admin.demo@ejemplo.test', 'Administración Técnica Demo'],
            'recepcion' => ['recepcion.demo@ejemplo.test', 'Recepción Demo'],
        ];

        $users = [];
        foreach ($definitions as $role => [$email, $name]) {
            $user = User::where('email', $email)->first();
            if ($user === null) {
                // La factory usa su contraseña por defecto: no se escribe ninguna credencial acá.
                $user = User::factory()->create([
                    'email' => $email,
                    'name' => $name,
                    'status' => 'active',
                    'password_changed_at' => now(),
                ]);
            }
            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
            $users[$role] = $user->fresh();
        }

        return $users;
    }

    private function family(): FamilyAccount
    {
        return FamilyAccount::firstOrCreate(
            ['account_code' => 'FA-DEMO001'],
            ['display_name' => 'Familia Demo Ficticia', 'status' => 'active', 'currency' => 'PYG'],
        );
    }

    /** @return array{0: Student, 1: Student} */
    private function students(FamilyAccount $family): array
    {
        $definitions = [
            ['ALU-2026-0041-7', 'Ana', 'Pérez', '1111111'],
            ['ALU-2026-0042-5', 'Bruno', 'López', '2222222'],
        ];

        $students = [];
        foreach ($definitions as [$code, $firstName, $lastName, $document]) {
            $student = Student::firstOrCreate(
                ['student_code' => $code],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'document_id' => $document,
                    'birth_date' => '2017-04-12',
                    'status' => 'active',
                    'admission_date' => now()->subMonths(8)->toDateString(),
                ],
            );
            FamilyAccountMember::firstOrCreate(
                ['family_account_id' => $family->id, 'student_id' => $student->id],
                ['role' => 'dependiente', 'valid_from' => now()->subMonths(8)->toDateString()],
            );
            $students[] = $student;
        }

        return [$students[0], $students[1]];
    }

    /** @param array<string, User> $users */
    private function guardians(Student $ana, Student $bruno, array $users): void
    {
        $definitions = [
            ['3333333', 'María', 'Pérez', $ana, true],
            ['4444444', 'Carlos', 'Pérez', $ana, false],
            ['5555555', 'José', 'López', $bruno, true],
        ];

        foreach ($definitions as [$document, $firstName, $lastName, $student, $wantsPrimary]) {
            $guardian = Guardian::firstOrCreate(
                ['document_id' => $document],
                ['first_name' => $firstName, 'last_name' => $lastName, 'status' => 'active', 'email' => 'tutor@ejemplo.test'],
            );
            $existing = \DB::table('student_guardians')->where('student_id', $student->id)->where('guardian_id', $guardian->id)->first();
            if ($existing !== null) {
                continue;
            }

            // El índice parcial permite un solo tutor principal vigente por alumno.
            $hasPrimary = \DB::table('student_guardians')
                ->where('student_id', $student->id)
                ->where('is_primary', true)
                ->whereNull('valid_until')
                ->exists();

            \DB::table('student_guardians')->insert([
                'student_id' => $student->id,
                'guardian_id' => $guardian->id,
                'relationship' => 'Madre/Padre',
                'is_primary' => $wantsPrimary && ! $hasPrimary,
                'can_pick_up' => true,
                'valid_from' => now()->subYear()->toDateString(),
            ]);
        }
    }

    private function pickup(Student $ana, User $director): void
    {
        AuthorizedPickup::firstOrCreate(
            ['document_id' => '5555550'],
            [
                'student_id' => $ana->id,
                'full_name' => 'Tercero Autorizado Demo',
                'phone' => '0981000000',
                'relationship' => 'Abuelo',
                'valid_from' => now()->subMonth()->toDateString(),
                'valid_until' => now()->addYear()->toDateString(),
                'is_active' => true,
                'created_by' => $director->id,
            ],
        );
    }

    /**
     * @param  array<string, User>  $users
     * @return array{0: Service, 1: Service, 2: Professional, 3: Professional, 4: Group}
     */
    private function scheduling(array $users, Student $ana, Student $bruno): array
    {
        $service1 = Service::firstOrCreate(
            ['name' => 'Terapia de Lenguaje — Demo'],
            ['modality' => 'both', 'default_duration_minutes' => 60, 'is_active' => true],
        );
        $service2 = Service::firstOrCreate(
            ['name' => 'Fisioterapia — Demo'],
            ['modality' => 'individual', 'default_duration_minutes' => 45, 'is_active' => true],
        );

        $pro1 = Professional::firstOrCreate(
            ['display_name' => 'Dra. Demo Ficticia'],
            ['specialty' => 'Fonoaudiología', 'status' => 'active', 'user_id' => $users['profesional']->id],
        );
        $pro2 = Professional::firstOrCreate(
            ['display_name' => 'Lic. Demo Segundo'],
            ['specialty' => 'Fisioterapia', 'status' => 'active'],
        );

        foreach ([$ana, $bruno] as $student) {
            Enrollment::firstOrCreate(
                ['student_id' => $student->id, 'service_id' => $service1->id],
                ['start_date' => now()->subMonths(3)->toDateString(), 'status' => 'active'],
            );
            ProfessionalAssignment::firstOrCreate(
                ['professional_id' => $pro1->id, 'student_id' => $student->id, 'role' => 'responsible'],
                ['valid_from' => now()->subMonths(3)->toDateString()],
            );
            ProfessionalAssignment::firstOrCreate(
                ['professional_id' => $pro2->id, 'student_id' => $student->id, 'role' => 'collaborator'],
                ['valid_from' => now()->subMonths(3)->toDateString()],
            );
        }
        Enrollment::firstOrCreate(
            ['student_id' => $ana->id, 'service_id' => $service2->id],
            ['start_date' => now()->subMonth()->toDateString(), 'status' => 'active'],
        );

        $group = Group::firstOrCreate(
            ['name' => 'Grupo Mañana — Demo'],
            [
                'service_id' => $service1->id,
                'responsible_professional_id' => $pro1->id,
                'room' => 'Sala 1',
                'capacity' => 6,
                'is_active' => true,
            ],
        );
        foreach ([$ana, $bruno] as $student) {
            GroupMembership::firstOrCreate(
                ['group_id' => $group->id, 'student_id' => $student->id],
                ['valid_from' => now()->subMonths(3)->toDateString()],
            );
        }

        AvailabilityRule::firstOrCreate(
            ['professional_id' => $pro1->id, 'weekday' => now()->dayOfWeek, 'start_time' => '09:00:00'],
            ['end_time' => '10:00:00', 'valid_from' => now()->subMonth()->toDateString()],
        );
        AvailabilityRule::firstOrCreate(
            ['professional_id' => $pro2->id, 'weekday' => now()->addDay()->dayOfWeek, 'start_time' => '11:00:00'],
            ['end_time' => '11:45:00', 'valid_from' => now()->subMonth()->toDateString()],
        );

        return [$service1, $service2, $pro1, $pro2, $group];
    }

    /**
     * @param  array<string, User>  $users
     * @return list<Session>
     */
    private function sessions(array $users, Service $service1, Service $service2, Professional $pro1, Professional $pro2, Group $group, Student $ana, Student $bruno): array
    {
        $data = [
            [$service1, now()->subDay()->setTime(9, 0)->toImmutable(), now()->subDay()->setTime(10, 0)->toImmutable(), [$ana->id, $bruno->id], [$pro1], $group->id, 'group'],
            [$service1, now()->setTime(10, 0)->toImmutable(), now()->setTime(11, 0)->toImmutable(), [$ana->id, $bruno->id], [$pro1, $pro2], $group->id, 'group'],
            [$service2, now()->addDay()->setTime(11, 0)->toImmutable(), now()->addDay()->setTime(11, 45)->toImmutable(), [$ana->id], [$pro2], null, 'individual'],
        ];

        $sessions = [];
        foreach ($data as [$service, $start, $end, $participants, $professionals, $groupId, $type]) {
            $existing = Session::where('service_id', $service->id)->where('starts_at', $start)->first();
            if ($existing !== null) {
                $sessions[] = $existing;

                continue;
            }
            $sessions[] = app(ScheduleSession::class)->execute(new ScheduleSessionData(
                serviceId: $service->id,
                startsAt: CarbonImmutable::instance($start),
                endsAt: CarbonImmutable::instance($end),
                participants: $participants,
                professionals: array_map(static fn (Professional $pro): array => ['id' => $pro->id, 'role' => 'responsible'], $professionals),
                groupId: $groupId,
                sessionType: $type,
                createdBy: $users['coordinacion']->id,
            ));
        }

        foreach (array_slice($sessions, 0, 2) as $session) {
            $session->update(['status' => 'completed']);
        }

        return $sessions;
    }

    /** @param list<Session> $sessions */
    private function attendance(array $sessions, User $recorder): void
    {
        foreach (array_slice($sessions, 0, 2) as $index => $session) {
            $records = $session->participants->map(function (SessionParticipant $participant) use ($index): array {
                return [
                    'participant_student_id' => $participant->student_id,
                    'status' => $index === 0 ? 'present' : 'excused',
                    'justification' => $index === 0 ? null : 'Inasistencia justificada por la familia.',
                ];
            })->all();

            if ($records === [] || AttendanceRecord::where('participant_id', $session->participants->first()->id)->exists()) {
                continue;
            }

            app(RecordAttendance::class)->execute(new RecordAttendanceData(
                sessionId: $session->id,
                records: $records,
                recordedBy: $recorder->id,
            ));
        }
    }

    /**
     * @param  array<string, User>  $users
     */
    private function care(array $users, Professional $pro1, Professional $pro2, Student $ana): ClinicalNote
    {
        $plan = CarePlan::firstOrCreate(
            ['title' => 'Plan de lenguaje — Demo'],
            [
                'student_id' => $ana->id,
                'diagnosis' => 'Diagnóstico ficticio de demostración.',
                'start_date' => now()->subMonths(2)->toDateString(),
                'status' => 'active',
                'responsible_professional_id' => $pro1->id,
                'created_by' => $users['coordinacion']->id,
            ],
        );
        $plan->professionals()->syncWithoutDetaching([
            $pro1->id => ['role' => 'responsible'],
            $pro2->id => ['role' => 'collaborator'],
        ]);

        foreach (['Objetivo: seguir indicaciones simples', 'Objetivo: ampliar vocabulario'] as $title) {
            CareGoal::firstOrCreate(
                ['care_plan_id' => $plan->id, 'title' => $title],
                ['status' => 'in_progress', 'target_date' => now()->addMonth()->toDateString()],
            );
        }

        $draft = ClinicalNote::firstOrCreate(
            ['summary' => 'Borrador de sesión — Demo'],
            [
                'student_id' => $ana->id,
                'author_user_id' => $users['profesional']->id,
                'care_plan_id' => $plan->id,
                'note_type' => 'session',
                'status' => 'draft',
                'content' => 'Borrador ficticio: observaciones pendientes de revisión.',
                'recorded_at' => now(),
            ],
        );

        $finalized = ClinicalNote::firstOrCreate(
            ['summary' => 'Nota finalizada — Demo'],
            [
                'student_id' => $ana->id,
                'author_user_id' => $users['profesional']->id,
                'care_plan_id' => $plan->id,
                'note_type' => 'evaluation',
                'status' => 'finalized',
                'content' => 'Observación clínica ficticia: evolución favorable del alumno.',
                'recorded_at' => now()->subDays(3),
                'finalized_at' => now()->subDays(3),
            ],
        );

        $correction = ClinicalNote::firstOrCreate(
            ['summary' => 'Corrección de nota — Demo'],
            [
                'student_id' => $ana->id,
                'author_user_id' => $users['profesional']->id,
                'care_plan_id' => $plan->id,
                'note_type' => 'evaluation',
                'status' => 'finalized',
                'content' => 'Contenido corregido ficticio sobre la misma sesión.',
                'supersedes_note_id' => $finalized->id,
                'correction_reason' => 'Se corrige el registro original tras revisión con la coordinación.',
                'recorded_at' => now()->subDay(),
                'finalized_at' => now()->subDay(),
            ],
        );
        if ($finalized->status !== 'superseded') {
            $finalized->update(['status' => 'superseded']);
        }

        ClinicalNoteAccessGrant::firstOrCreate(
            ['reason' => 'Informe de avance solicitado por la dirección general.'],
            [
                'clinical_note_id' => $correction->id,
                'student_id' => $ana->id,
                'granted_to_user_id' => $users['coordinacion']->id,
                'granted_by_user_id' => $users['direccion']->id,
                'expires_at' => now()->addDays(7),
            ],
        );
        ClinicalNoteAccessGrant::firstOrCreate(
            ['reason' => 'Acceso de auditoría ficticio ya vencido para la demo.'],
            [
                'clinical_note_id' => $correction->id,
                'student_id' => $ana->id,
                'granted_to_user_id' => $users['admin_tecnico']->id,
                'granted_by_user_id' => $users['direccion']->id,
                'expires_at' => now()->subDay(),
            ],
        );
        ClinicalNoteAccessGrant::firstOrCreate(
            ['reason' => 'Grant de demostración revocado por cambio de informe.'],
            [
                'clinical_note_id' => $finalized->id,
                'student_id' => $ana->id,
                'granted_to_user_id' => $users['finanzas']->id,
                'granted_by_user_id' => $users['direccion']->id,
                'expires_at' => now()->addDays(7),
                'revoked_at' => now(),
                'revoked_by_user_id' => $users['direccion']->id,
                'revocation_reason' => 'Revocado por cambio de informe.',
            ],
        );

        app(ClinicalNoteReadAudit::class)->record($correction, $users['profesional']);

        return $correction;
    }

    /** @param array<string, User> $users */
    private function billing(array $users, FamilyAccount $family, Student $ana, Student $bruno, Service $service1, Service $service2): void
    {
        $plan1 = FeePlan::firstOrCreate(
            ['name' => 'Cuota terapia de lenguaje — Demo'],
            ['service_id' => $service1->id, 'amount' => '300000.00', 'periodicity' => 'monthly', 'is_active' => true, 'valid_from' => now()->subMonths(3)->toDateString()],
        );
        $plan2 = FeePlan::firstOrCreate(
            ['name' => 'Cuota fisioterapia — Demo'],
            ['service_id' => $service2->id, 'amount' => '250000.00', 'periodicity' => 'monthly', 'is_active' => true, 'valid_from' => now()->subMonths(3)->toDateString()],
        );

        $chargeA = $this->charge($family, $ana, 'Cuota demo — Ana', '300000.00', now()->addDays(10), 'pending', $plan1);
        $chargeB = $this->charge($family, $bruno, 'Cuota demo — Bruno', '300000.00', now()->addDays(10), 'pending', $plan1);
        $chargeC = $this->charge($family, $bruno, 'Cuota demo vencida — Bruno', '150000.00', now()->subDays(5), 'pending', $plan2);

        $cash = CashSession::firstOrCreate(
            ['notes' => 'Caja demo del día'],
            [
                'user_id' => $users['finanzas']->id,
                'opened_at' => now()->startOfDay(),
                'closed_at' => now(),
                'opening_amount' => '50000.00',
                'closing_amount' => '775000.00',
                'expected_amount' => '775000.00',
                'difference' => '0.00',
                'status' => 'closed',
                'closed_by' => $users['finanzas']->id,
            ],
        );

        $payment = Payment::firstOrCreate(
            ['reference' => 'DEMO-TRANSF-001'],
            [
                'family_account_id' => $family->id,
                'amount' => '500000.00',
                'paid_at' => now(),
                'method' => 'bank_transfer',
                'payer_name' => 'Familia Demo Ficticia',
                'status' => 'confirmed',
                'received_by' => $users['finanzas']->id,
            ],
        );
        $cashPayment = Payment::firstOrCreate(
            ['reference' => 'DEMO-EFECTIVO-002'],
            [
                'family_account_id' => $family->id,
                'cash_session_id' => $cash->id,
                'amount' => '250000.00',
                'paid_at' => now(),
                'method' => 'cash',
                'status' => 'confirmed',
                'received_by' => $users['finanzas']->id,
            ],
        );

        foreach ([[$payment, $chargeA, '300000.00'], [$payment, $chargeB, '200000.00'], [$cashPayment, $chargeC, '150000.00']] as [$pay, $charge, $amount]) {
            PaymentAllocation::firstOrCreate(
                ['payment_id' => $pay->id, 'charge_id' => $charge->id],
                ['amount' => $amount],
            );
        }
        $chargeA->update(['amount_paid' => '300000.00', 'status' => 'paid']);
        $chargeB->update(['amount_paid' => '200000.00', 'status' => 'partial']);
        $chargeC->update(['amount_paid' => '150000.00', 'status' => 'paid']);

        $this->receipt($payment, $users['finanzas']);
        $this->receipt($cashPayment, $users['finanzas']);

        FinancialAdjustment::firstOrCreate(
            ['reason' => 'Descuento ficticio autorizado por dirección para la demo.'],
            [
                'charge_id' => $chargeB->id,
                'family_account_id' => $family->id,
                'kind' => 'discount',
                'amount' => '10000.00',
                'authorized_by' => $users['direccion']->id,
                'created_by' => $users['finanzas']->id,
            ],
        );

        app(Auditor::class)->record('payment.registered', Payment::class, $payment->id,
            ['amount' => '500000.00', 'method' => 'bank_transfer'], $users['finanzas']->id, $family->id, 'warning');
    }

    private function charge(FamilyAccount $family, Student $student, string $description, string $amount, $dueDate, string $status, FeePlan $plan): Charge
    {
        return Charge::firstOrCreate(
            ['family_account_id' => $family->id, 'student_id' => $student->id, 'description' => $description],
            [
                'fee_plan_id' => $plan->id,
                'due_date' => $dueDate->toDateString(),
                'amount' => $amount,
                'status' => $status,
            ],
        );
    }

    private function receipt(Payment $payment, User $issuer): void
    {
        if ($payment->receipt !== null) {
            return;
        }

        $year = (int) $payment->paid_at->year;
        $sequence = ReceiptSequence::firstOrCreate(['year' => $year, 'prefix' => 'REC'], ['last_number' => 0]);
        $sequence->increment('last_number');
        $number = sprintf('REC-%d-%06d', $year, $sequence->last_number);

        Receipt::create([
            'payment_id' => $payment->id,
            'receipt_number' => $number,
            'issued_at' => now(),
            'status' => 'issued',
            'issued_by' => $issuer->id,
            'total' => $payment->amount,
        ]);
    }

    /** @param array<string, User> $users */
    private function identityExtras(array $users, ClinicalNote $note): void
    {
        Setting::firstOrCreate(
            ['key' => 'demo_mode'],
            ['value' => 'true', 'type' => 'boolean', 'group_name' => 'demo', 'is_public' => true, 'description' => 'Demo con datos ficticios', 'updated_by' => $users['admin_tecnico']->id],
        );
        FeatureFlag::firstOrCreate(
            ['key' => 'portal_familias_demo'],
            ['description' => 'Flag ficticio para mostrar el recurso en el panel', 'is_enabled' => false, 'scope' => 'all', 'updated_by' => $users['admin_tecnico']->id],
        );
        SecurityEvent::firstOrCreate(
            ['event_type' => 'grant.issued', 'user_id' => $users['coordinacion']->id],
            ['severity' => 'warning', 'student_id' => $note->student_id, 'resource_type' => ClinicalNote::class, 'resource_id' => $note->id, 'reason' => 'Demo grant', 'created_at' => now()],
        );
    }
}
