<?php

declare(strict_types=1);

use Cai\Care\Actions\FinalizeClinicalNote;
use Cai\Care\Actions\IssueClinicalNoteGrant;
use Cai\Care\Actions\RevokeClinicalNoteGrant;
use Cai\Care\Actions\SupersedeClinicalNote;
use Cai\Care\Data\FinalizeClinicalNoteData;
use Cai\Care\Data\IssueClinicalNoteGrantData;
use Cai\Care\Data\RevokeClinicalNoteGrantData;
use Cai\Care\Data\SupersedeClinicalNoteData;
use Cai\Care\Enums\ClinicalNoteStatus;
use Cai\Care\Exceptions\NoteAlreadyFinalizedException;
use Cai\Care\Exceptions\NotFinalizedException;
use Cai\Care\Exceptions\NotNoteAuthorException;
use Cai\Care\Exceptions\ReasonTooShortException;
use Cai\Care\Jobs\ExpireClinicalNoteGrants;
use Cai\Care\Models\ClinicalNote;
use Cai\Care\Models\ClinicalNoteAccessGrant;
use Cai\Identity\Models\SecurityEvent;
use Cai\Identity\Models\User;
use Cai\Scheduling\Models\Professional;
use Cai\Scheduling\Models\ProfessionalAssignment;
use Cai\Students\Models\Student;
use Database\Seeders\IdentitySeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed(IdentitySeeder::class);
    config(['cai.seguridad.clinical_encryption_key' => base64_encode(random_bytes(32))]);
});

function clinicalDraft(User $author, ?Student $student = null): ClinicalNote
{
    return ClinicalNote::create([
        'student_id' => ($student ?? Student::factory()->create())->id,
        'author_user_id' => $author->id,
        'note_type' => 'session',
        'status' => ClinicalNoteStatus::Draft->value,
        'content' => 'Contenido clinico original del alumno.',
        'summary' => 'Resumen neutro',
        'recorded_at' => now(),
    ]);
}

function clinicalDirector(): User
{
    $user = User::factory()->create(['password_changed_at' => now()]);
    $user->assignRole('direccion');

    return $user->fresh();
}

it('finalizes a draft once and keeps finalized notes immutable', function () {
    $author = clinicalDirector();
    $note = clinicalDraft($author);
    $action = app(FinalizeClinicalNote::class);

    $finalized = $action->execute(new FinalizeClinicalNoteData($note->id, $author->id));

    expect($finalized->status)->toBe(ClinicalNoteStatus::Finalized->value)
        ->and($finalized->finalized_at)->not->toBeNull()
        ->and(DB::table('audit_events')->where('action', 'clinical_note.finalized')->exists())->toBeTrue();

    expect(fn () => $action->execute(new FinalizeClinicalNoteData($note->id, $author->id)))
        ->toThrow(NoteAlreadyFinalizedException::class);

    expect(fn () => $action->execute(new FinalizeClinicalNoteData($note->id, User::factory()->create()->id)))
        ->toThrow(NoteAlreadyFinalizedException::class);
});

it('refuses to finalize a note belonging to another author', function () {
    $note = clinicalDraft(clinicalDirector());
    $other = clinicalDirector();

    expect(fn () => app(FinalizeClinicalNote::class)->execute(new FinalizeClinicalNoteData($note->id, $other->id)))
        ->toThrow(NotNoteAuthorException::class);
});

it('corrects a finalized note with a new note and a reason of at least 20 characters', function () {
    $author = clinicalDirector();
    $original = clinicalDraft($author);
    app(FinalizeClinicalNote::class)->execute(new FinalizeClinicalNoteData($original->id, $author->id));

    expect(fn () => app(SupersedeClinicalNote::class)->execute(new SupersedeClinicalNoteData(
        $original->id, $author->id, 'Contenido corregido', 'Motivo corto',
    )))->toThrow(ReasonTooShortException::class);

    $correction = app(SupersedeClinicalNote::class)->execute(new SupersedeClinicalNoteData(
        $original->id,
        $author->id,
        'Contenido corregido y verificado sobre el alumno.',
        'Se corrige el curso de la sesion segun revision de la coordinacion.',
    ));

    expect($correction->status)->toBe(ClinicalNoteStatus::Finalized->value)
        ->and($correction->supersedes_note_id)->toBe($original->id)
        ->and($correction->content)->toBe('Contenido corregido y verificado sobre el alumno.')
        ->and($original->fresh()->status)->toBe(ClinicalNoteStatus::Superseded->value)
        ->and(DB::table('audit_events')->where('action', 'clinical_note.superseded')->exists())->toBeTrue();
});

it('does not supersede a draft', function () {
    $author = clinicalDirector();
    $note = clinicalDraft($author);

    expect(fn () => app(SupersedeClinicalNote::class)->execute(new SupersedeClinicalNoteData(
        $note->id, $author->id, 'Otro contenido', 'Motivo suficientemente largo para la correccion.',
    )))->toThrow(NotFinalizedException::class);
});

it('requires a reason of at least 20 characters to issue a grant and caps its duration', function () {
    $director = clinicalDirector();
    $student = Student::factory()->create();
    $beneficiary = User::factory()->create();
    $action = app(IssueClinicalNoteGrant::class);

    expect(fn () => $action->execute(new IssueClinicalNoteGrantData(
        $director->id, $beneficiary->id, $student->id, 'Motivo corto',
    )))->toThrow(ReasonTooShortException::class);

    $grant = $action->execute(new IssueClinicalNoteGrantData(
        $director->id, $beneficiary->id, $student->id, 'Informe de avance solicitado por la direccion.', days: 100,
    ));

    expect(abs($grant->expires_at->diffInDays(now())))->toBeGreaterThanOrEqual(29)
        ->and(abs($grant->expires_at->diffInDays(now())))->toBeLessThanOrEqual(30)
        ->and($grant->isActive())->toBeTrue()
        ->and(SecurityEvent::where('event_type', 'grant.issued')->exists())->toBeTrue();
});

it('revokes a grant and removes access immediately', function () {
    $director = clinicalDirector();
    $student = Student::factory()->create();
    $beneficiary = User::factory()->create();
    $note = clinicalDraft(clinicalDirector(), $student);

    $grant = app(IssueClinicalNoteGrant::class)->execute(new IssueClinicalNoteGrantData(
        $director->id, $beneficiary->id, $student->id, 'Informe de avance solicitado por la direccion.',
        clinicalNoteId: $note->id,
    ));

    expect($note->hasActiveGrantFor($beneficiary->id))->toBeTrue();

    app(RevokeClinicalNoteGrant::class)->execute(new RevokeClinicalNoteGrantData(
        $grant->id, $director->id, 'Revocado por cambio de objetivo del informe.',
    ));

    expect($note->fresh()->hasActiveGrantFor($beneficiary->id))->toBeFalse()
        ->and(SecurityEvent::where('event_type', 'grant.revoked')->exists())->toBeTrue();
});

it('expires grants automatically with a queued job', function () {
    $director = clinicalDirector();
    $student = Student::factory()->create();
    $beneficiary = User::factory()->create();
    $note = clinicalDraft(clinicalDirector(), $student);

    $grant = app(IssueClinicalNoteGrant::class)->execute(new IssueClinicalNoteGrantData(
        $director->id, $beneficiary->id, $student->id, 'Informe de avance solicitado por la direccion.', days: 7,
    ));

    $this->travel(8)->days();

    expect((new ExpireClinicalNoteGrants)->handle())->toBe(1)
        ->and($grant->fresh()->revoked_at)->not->toBeNull()
        ->and($grant->fresh()->revocation_reason)->toBe('Expiración automática')
        ->and($note->fresh()->hasActiveGrantFor($beneficiary->id))->toBeFalse()
        ->and(SecurityEvent::where('event_type', 'grant.expired')->exists())->toBeTrue();

    expect(ClinicalNoteAccessGrant::whereNull('revoked_at')->count())->toBe(0);
});

it('keeps the professional assignment scope when a note is finalized', function () {
    $professional = Professional::create(['display_name' => 'Profesional clinico', 'status' => 'active']);
    $author = User::factory()->create(['password_changed_at' => now(), 'professional_id' => $professional->id]);
    $author->assignRole('profesional');
    $student = Student::factory()->create();
    ProfessionalAssignment::create([
        'professional_id' => $professional->id,
        'student_id' => $student->id,
        'role' => 'responsible',
        'valid_from' => now()->subMonth()->toDateString(),
    ]);

    $note = clinicalDraft($author->fresh(), $student);
    $finalized = app(FinalizeClinicalNote::class)->execute(new FinalizeClinicalNoteData($note->id, $author->id));

    expect($finalized->status)->toBe(ClinicalNoteStatus::Finalized->value);
});
