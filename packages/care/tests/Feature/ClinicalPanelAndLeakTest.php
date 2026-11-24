<?php

declare(strict_types=1);

use Cai\Care\Audit\ClinicalNoteReadAudit;
use Cai\Care\Enums\ClinicalNoteStatus;
use Cai\Care\Models\ClinicalNote;
use Cai\Identity\Authorization\RoleCatalog;
use Cai\Identity\Models\User;
use Cai\Scheduling\Models\Professional;
use Cai\Students\Models\Student;
use Cai\Students\Services\StudentDirectoryService;
use Database\Seeders\IdentitySeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed(IdentitySeeder::class);
    config(['cai.seguridad.clinical_encryption_key' => base64_encode(random_bytes(32))]);
});

function leakAuthor(): array
{
    $student = Student::factory()->create();
    $professional = Professional::create(['display_name' => 'Profesional filtro', 'status' => 'active']);
    $user = User::factory()->create(['password_changed_at' => now()]);
    $user->assignRole('profesional');
    $professional->update(['user_id' => $user->id]);

    return [$user->fresh(), $student];
}

function leakNote(User $author, Student $student): ClinicalNote
{
    return ClinicalNote::create([
        'student_id' => $student->id,
        'author_user_id' => $author->id,
        'note_type' => 'session',
        'status' => ClinicalNoteStatus::Finalized->value,
        'content' => 'OBSERVACION_CLINICA_UNICA_NO_FILTRABLE',
        'summary' => 'Resumen permitido en el listado',
        'recorded_at' => now(),
        'finalized_at' => now(),
    ]);
}

it('registers the four care resources in the panel', function () {
    foreach (['care-plans', 'care-goals', 'clinical-notes', 'clinical-grants'] as $slug) {
        expect(route('filament.panel.resources.'.$slug.'.index'))->toContain('/panel/'.$slug);
    }
});

it('matches the 04.02 matrix for every role on every care resource URL', function () {
    $resources = [
        'care-plans' => 'care.plans.view',
        'care-goals' => 'care.goals.manage',
        'clinical-notes' => 'care.notes.view',
        'clinical-grants' => 'care.grants.issue',
    ];

    foreach (RoleCatalog::all() as $roleName => $role) {
        foreach ($resources as $resource => $permission) {
            $user = User::factory()->create(['password_changed_at' => now()]);
            $user->assignRole($roleName);
            $expectedStatus = $role->hasPermission($permission) ? 200 : 403;

            expect($this->actingAs($user->fresh())->get('/panel/'.$resource)->getStatusCode())
                ->toBe($expectedStatus, "{$roleName} sobre /panel/{$resource} ({$permission})");
        }
    }
});

it('does not render clinical content in the notes table', function () {
    [$user, $student] = leakAuthor();
    $note = leakNote($user, $student);

    $response = $this->actingAs($user)->get('/panel/clinical-notes');

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->not->toContain('OBSERVACION_CLINICA_UNICA_NO_FILTRABLE')
        ->and($response->getContent())->toContain('Resumen permitido en el listado');
});

it('denies a direct note URL to technical administration', function () {
    [$user, $student] = leakAuthor();
    $note = leakNote($user, $student);
    $admin = User::factory()->create(['password_changed_at' => now()]);
    $admin->assignRole('admin_tecnico');

    $response = $this->actingAs($admin->fresh())->get('/panel/clinical-notes/'.$note->id);

    expect($response->getStatusCode())->toBeIn([403, 404])
        ->and($response->getContent())->not->toContain('OBSERVACION_CLINICA_UNICA_NO_FILTRABLE');
});

it('records an audit entry when clinical content is read', function () {
    [$user, $student] = leakAuthor();
    $note = leakNote($user, $student);

    app(ClinicalNoteReadAudit::class)->record($note, $user);

    expect(DB::table('audit_events')
        ->where('action', 'clinical_note.viewed')
        ->where('entity_id', $note->id)
        ->where('actor_user_id', $user->id)
        ->exists())->toBeTrue();

    $response = $this->actingAs($user)->get('/panel/clinical-notes/'.$note->id);

    expect($response->getStatusCode())->toBe(200)
        ->and(DB::table('audit_events')->where('action', 'clinical_note.viewed')->count())->toBeGreaterThan(1);
});

it('does not leak clinical content through the student directory summary', function () {
    [$user, $student] = leakAuthor();
    leakNote($user, $student);

    $summary = app(StudentDirectoryService::class)->find($student->id);
    $serialized = json_encode($summary, JSON_THROW_ON_ERROR);

    expect($serialized)->not->toContain('OBSERVACION_CLINICA_UNICA_NO_FILTRABLE')
        ->and($student->fresh()->toArray())->not->toHaveKey('content');
});
