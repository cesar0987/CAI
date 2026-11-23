<?php

declare(strict_types=1);

use Cai\Care\Models\ClinicalNote;
use Cai\Care\Models\ClinicalNoteAccessGrant;
use Cai\Identity\Models\User;
use Cai\Students\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Clave generada en memoria para el test: nunca se escribe en .env ni en el repo.
    config(['cai.seguridad.clinical_encryption_key' => base64_encode(random_bytes(32))]);
});

function careNote(): ClinicalNote
{
    $student = Student::factory()->create();
    $author = User::factory()->create();

    return ClinicalNote::create([
        'student_id' => $student->id,
        'author_user_id' => $author->id,
        'note_type' => 'session',
        'status' => 'draft',
        'content' => 'Contenido ultra sensible sobre el alumno.',
        'summary' => 'Resumen sin datos clinicos',
        'recorded_at' => now(),
    ]);
}

it('creates the five care tables documented in 03.05', function () {
    foreach (['care_plans', 'care_plan_professionals', 'care_goals', 'clinical_notes', 'clinical_note_access_grants'] as $table) {
        expect(Schema::hasTable($table))->toBeTrue($table);
    }
});

it('encrypts the clinical note content at rest with AES-256-GCM', function () {
    $note = careNote();

    $raw = DB::table('clinical_notes')->where('id', $note->id)->value('content');

    expect($raw)->not->toContain('ultra sensible')
        ->and($raw)->not->toBe('Contenido ultra sensible sobre el alumno.')
        ->and($note->fresh()->content)->toBe('Contenido ultra sensible sobre el alumno.');
});

it('fails closed when the clinical encryption key is missing', function () {
    config(['cai.seguridad.clinical_encryption_key' => null]);

    expect(fn () => careNote())->toThrow(RuntimeException::class);
});

it('only treats a grant as active while it is not revoked and not expired', function () {
    $note = careNote();
    $user = User::factory()->create();

    $grant = ClinicalNoteAccessGrant::create([
        'clinical_note_id' => $note->id,
        'student_id' => $note->student_id,
        'granted_to_user_id' => $user->id,
        'granted_by_user_id' => $note->author_user_id,
        'reason' => 'Solicitud de Dirección para informe de avance',
        'expires_at' => now()->addDays(7),
    ]);

    expect($note->hasActiveGrantFor($user->id))->toBeTrue();

    $grant->update(['revoked_at' => now()]);

    expect($note->fresh()->hasActiveGrantFor($user->id))->toBeFalse();
});
