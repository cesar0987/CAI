<?php

declare(strict_types=1);

use Cai\Care\Actions\CreateClinicalNote;
use Cai\Care\Actions\IssueClinicalNoteGrant;
use Cai\Care\Data\CreateClinicalNoteData;
use Cai\Care\Data\IssueClinicalNoteGrantData;
use Cai\Care\Enums\ClinicalNoteStatus;
use Cai\Care\Exceptions\ClinicalScopeException;
use Cai\Care\Filament\Resources\CareGoalResource;
use Cai\Care\Filament\Resources\CarePlanResource;
use Cai\Care\Filament\Resources\ClinicalNoteAccessGrantResource;
use Cai\Care\Filament\Resources\ClinicalNoteResource;
use Cai\Care\Models\ClinicalNote;
use Cai\Identity\Models\User;
use Cai\Scheduling\Models\Professional;
use Cai\Scheduling\Models\ProfessionalAssignment;
use Cai\Students\Models\Student;
use Database\Seeders\IdentitySeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->seed(IdentitySeeder::class);
    config(['cai.seguridad.clinical_encryption_key' => base64_encode(random_bytes(32))]);
});

function policyProfessional(bool $withAssignment = true): array
{
    $student = Student::factory()->create();
    $professional = Professional::create(['display_name' => 'Profesional policy', 'status' => 'active']);
    $user = User::factory()->create(['password_changed_at' => now()]);
    $user->assignRole('profesional');
    $professional->update(['user_id' => $user->id]);

    if ($withAssignment) {
        ProfessionalAssignment::create([
            'professional_id' => $professional->id,
            'student_id' => $student->id,
            'role' => 'responsible',
            'valid_from' => now()->subMonth()->toDateString(),
        ]);
    }

    return [$user->fresh(), $student, $professional];
}

function policyNote(User $author, Student $student, ClinicalNoteStatus $status = ClinicalNoteStatus::Finalized): ClinicalNote
{
    return ClinicalNote::create([
        'student_id' => $student->id,
        'author_user_id' => $author->id,
        'note_type' => 'session',
        'status' => $status->value,
        'content' => 'Contenido sensible de la nota.',
        'summary' => 'Resumen sin dato clinico',
        'recorded_at' => now(),
        'finalized_at' => $status === ClinicalNoteStatus::Finalized ? now() : null,
    ]);
}

function policyRoleUser(string $role): User
{
    $user = User::factory()->create(['password_changed_at' => now()]);
    $user->assignRole($role);

    return $user->fresh();
}

it('allows the assigned professional to view the note and denies one without assignment', function () {
    [$user, $student] = policyProfessional();
    $note = policyNote(policyRoleUser('direccion'), $student);

    expect(Gate::forUser($user)->allows('view', $note))->toBeTrue()
        ->and(ClinicalNote::visibleTo($user)->whereKey($note->id)->exists())->toBeTrue();

    [$unassigned, $otherStudent] = policyProfessional(false);
    $otherNote = policyNote(policyRoleUser('direccion'), $otherStudent);

    expect(Gate::forUser($unassigned)->allows('view', $otherNote))->toBeFalse()
        ->and(ClinicalNote::visibleTo($unassigned)->whereKey($otherNote->id)->exists())->toBeFalse();
});

it('always allows the author but never allows deleting a note', function () {
    [$user, $student] = policyProfessional();
    $note = policyNote($user, $student);
    $other = policyRoleUser('direccion');

    expect(Gate::forUser($user)->allows('view', $note))->toBeTrue()
        ->and(Gate::forUser($other)->allows('view', $note))->toBeFalse()
        ->and(Gate::forUser($user)->allows('delete', $note))->toBeFalse()
        ->and(Gate::forUser($user)->allows('forceDelete', $note))->toBeFalse();
});

it('denies clinical notes to technical administration, finance and secretariat', function () {
    $note = policyNote(policyRoleUser('direccion'), Student::factory()->create());

    foreach (['admin_tecnico', 'finanzas', 'secretaria', 'recepcion'] as $role) {
        expect(Gate::forUser(policyRoleUser($role))->allows('view', $note))->toBeFalse($role);
    }
});

it('allows access with an active grant and denies it once expired', function () {
    [$user, $student] = policyProfessional(false);
    $note = policyNote(policyRoleUser('direccion'), $student);
    $director = policyRoleUser('direccion');

    expect(Gate::forUser($user)->allows('view', $note))->toBeFalse();

    app(IssueClinicalNoteGrant::class)->execute(new IssueClinicalNoteGrantData(
        $director->id,
        $user->id,
        $student->id,
        'Solicitud de Dirección para informe de avance.',
        clinicalNoteId: $note->id,
        days: 2,
    ));

    expect(Gate::forUser($user)->allows('view', $note))->toBeTrue()
        ->and(ClinicalNote::visibleTo($user)->whereKey($note->id)->exists())->toBeTrue();

    $this->travel(3)->days();

    expect(Gate::forUser($user)->allows('view', $note))->toBeFalse()
        ->and(ClinicalNote::visibleTo($user)->whereKey($note->id)->exists())->toBeFalse();
});

it('blocks note creation when there is no active assignment', function () {
    [$user, $student] = policyProfessional(false);

    expect(Gate::forUser($user)->allows('create', ClinicalNote::class))->toBeTrue()
        ->and(fn () => app(CreateClinicalNote::class)->execute(new CreateClinicalNoteData(
            $user->id, $student->id, 'session', 'Contenido clinico.',
        )))->toThrow(ClinicalScopeException::class);
});

it('only allows editing and finalizing a draft by its author', function () {
    [$user, $student] = policyProfessional();
    $draft = policyNote($user, $student, ClinicalNoteStatus::Draft);
    $finalized = policyNote($user, $student);

    expect(Gate::forUser($user)->allows('update', $draft))->toBeTrue()
        ->and(Gate::forUser($user)->allows('finalize', $draft))->toBeTrue()
        ->and(Gate::forUser($user)->allows('update', $finalized))->toBeFalse()
        ->and(Gate::forUser($user)->allows('finalize', $finalized))->toBeFalse()
        ->and(Gate::forUser(policyRoleUser('profesional'))->allows('finalize', $draft))->toBeFalse();
});

it('keeps care resources out of Filament global search', function () {
    foreach ([ClinicalNoteResource::class, CarePlanResource::class, CareGoalResource::class, ClinicalNoteAccessGrantResource::class] as $resource) {
        expect($resource::canGloballySearch())->toBeFalse($resource);
    }
});
