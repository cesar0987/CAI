<?php

declare(strict_types=1);

use Cai\Identity\Authorization\RoleCatalog;
use Cai\Identity\Models\User;
use Cai\Students\Models\Student;
use Database\Seeders\IdentitySeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->seed(IdentitySeeder::class);
});

function studentsPanelUser(string $roleName): User
{
    $user = User::factory()->create(['password_changed_at' => now()]);
    $user->assignRole($roleName);

    return $user->fresh();
}

it('registers the four F3 resources in the panel', function () {
    foreach (['students', 'guardians', 'family-accounts', 'authorized-pickups'] as $slug) {
        expect(route('filament.panel.resources.'.$slug.'.index'))->toContain('/panel/'.$slug);
    }
});

it('matches the 04.02 matrix for every role on every student resource URL', function () {
    $resources = [
        'students' => 'students.students.view',
        'guardians' => 'students.guardians.manage',
        'family-accounts' => 'students.family_accounts.view',
        'authorized-pickups' => 'students.authorized_pickups.manage',
    ];

    foreach (RoleCatalog::all() as $roleName => $role) {
        foreach ($resources as $resource => $permission) {
            $user = studentsPanelUser($roleName);
            $expectedStatus = $role->hasPermission($permission) ? 200 : 403;

            expect($this->actingAs($user)->get('/panel/'.$resource)->getStatusCode())
                ->toBe($expectedStatus, "{$roleName} sobre /panel/{$resource} ({$permission})");
        }
    }
});

it('redirects guests to the panel login', function () {
    foreach (['students', 'guardians', 'family-accounts', 'authorized-pickups'] as $resource) {
        $this->get('/panel/'.$resource)->assertRedirect(route('filament.panel.auth.login'));
    }
});

it('keeps recepcion out of the student list but lets it register pickups', function () {
    $recepcion = studentsPanelUser('recepcion');

    $this->actingAs($recepcion)->get('/panel/students')->assertForbidden();
    $this->actingAs($recepcion)->get('/panel/authorized-pickups')->assertOk();
    $this->actingAs($recepcion)->get('/panel/guardians')->assertOk();
    $this->actingAs($recepcion)->get('/panel/family-accounts')->assertForbidden();
});

it('applies the visibility scope to the student table', function () {
    $student = Student::factory()->create(['first_name' => 'Ana', 'last_name' => 'Pérez']);

    $this->actingAs(studentsPanelUser('direccion'))
        ->get('/panel/students')
        ->assertOk()
        ->assertSee($student->student_code);

    // Profesional tiene care.clinical.read: 04.03 §3 le da visión global.
    $this->actingAs(studentsPanelUser('profesional'))
        ->get('/panel/students')
        ->assertOk()
        ->assertSee($student->student_code);

    // Finanzas tiene students.students.view pero no care.clinical.read; en F3
    // no hay asignaciones, así que el scope falla cerrado (04.03 §3).
    $this->actingAs(studentsPanelUser('finanzas'))
        ->get('/panel/students')
        ->assertOk()
        ->assertDontSee($student->student_code);
});

it('gates write screens with the create and manage permissions', function () {
    $this->actingAs(studentsPanelUser('direccion'))->get('/panel/students/create')->assertOk();
    $this->actingAs(studentsPanelUser('secretaria'))->get('/panel/guardians/create')->assertOk();
    $this->actingAs(studentsPanelUser('finanzas'))->get('/panel/students/create')->assertForbidden();
    $this->actingAs(studentsPanelUser('recepcion'))->get('/panel/students/create')->assertForbidden();
});

it('never exposes hard delete actions for student records', function () {
    $student = Student::factory()->create();

    expect(Gate::forUser(studentsPanelUser('direccion'))->denies('delete', $student))->toBeTrue();
});
