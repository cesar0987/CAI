<?php

declare(strict_types=1);

use Cai\Identity\Authorization\RoleCatalog;
use Cai\Identity\Models\User;
use Cai\Scheduling\Actions\ScheduleSession;
use Cai\Scheduling\Data\ScheduleSessionData;
use Cai\Scheduling\Models\Professional;
use Cai\Scheduling\Models\Service;
use Cai\Scheduling\Models\Session;
use Cai\Students\Models\Student;
use Database\Seeders\IdentitySeeder;

beforeEach(function () {
    $this->seed(IdentitySeeder::class);
});

function schedPanelUser(string $roleName): User
{
    $user = User::factory()->create(['password_changed_at' => now()]);
    $user->assignRole($roleName);

    return $user->fresh();
}

it('registers the seven scheduling resources and the calendar page', function () {
    foreach (['services', 'professionals', 'enrollments', 'groups', 'sessions', 'attendance'] as $slug) {
        expect(route('filament.panel.resources.'.$slug.'.index'))->toContain('/panel/'.$slug);
    }
});

it('matches the 04.02 matrix for every role on every scheduling resource URL', function () {
    $resources = [
        'services' => 'scheduling.services.view',
        'professionals' => 'scheduling.professionals.manage',
        'enrollments' => 'scheduling.enrollments.view',
        'groups' => 'scheduling.groups.manage',
        'sessions' => 'scheduling.sessions.view',
        'attendance' => 'scheduling.attendance.record',
    ];

    foreach (RoleCatalog::all() as $roleName => $role) {
        foreach ($resources as $resource => $permission) {
            $expectedStatus = $role->hasPermission($permission) ? 200 : 403;

            expect($this->actingAs(schedPanelUser($roleName))->get('/panel/'.$resource)->getStatusCode())
                ->toBe($expectedStatus, "{$roleName} sobre /panel/{$resource} ({$permission})");
        }
    }
});

it('redirects guests to the panel login', function () {
    foreach (['services', 'professionals', 'enrollments', 'groups', 'sessions', 'attendance'] as $resource) {
        $this->get('/panel/'.$resource)->assertRedirect(route('filament.panel.auth.login'));
    }
});

it('gates write screens with the canonical permissions', function () {
    $this->actingAs(schedPanelUser('direccion'))->get('/panel/services/create')->assertOk();
    $this->actingAs(schedPanelUser('recepcion'))->get('/panel/services/create')->assertForbidden();
    $this->actingAs(schedPanelUser('finanzas'))->get('/panel/sessions/create')->assertForbidden();
    $this->actingAs(schedPanelUser('coordinacion'))->get('/panel/sessions/create')->assertOk();
});

it('keeps a professional out of sessions they are not responsible for', function () {
    $service = Service::create(['name' => 'Fisioterapia', 'modality' => 'individual']);
    $ownProfessional = Professional::create(['display_name' => 'Profesional propio']);
    $otherProfessional = Professional::create(['display_name' => 'Otro profesional']);
    $student = Student::factory()->create();

    $user = User::factory()->create(['password_changed_at' => now()]);
    $user->assignRole('profesional');
    $ownProfessional->update(['user_id' => $user->id]);

    $makeSession = static function (Professional $professional, int $hour) use ($service, $student): Session {
        $start = now()->addDays($hour)->setTime(10, 0)->toImmutable();

        return app(ScheduleSession::class)->execute(new ScheduleSessionData(
            serviceId: $service->id,
            startsAt: $start,
            endsAt: $start->addHour(),
            participants: [$student->id],
            professionals: [['id' => $professional->id, 'role' => 'responsible']],
        ));
    };

    $own = $makeSession($ownProfessional, 1);
    $other = $makeSession($otherProfessional, 2);

    $this->actingAs($user->fresh())
        ->get('/panel/sessions/'.$own->id.'/edit')
        ->assertOk();
    // El scope excluye el registro: Filament responde 404 en vez de 403.
    $this->actingAs($user->fresh())
        ->get('/panel/sessions/'.$other->id.'/edit')
        ->assertNotFound();
});

it('serves the calendar only to roles that may view the agenda', function () {
    $this->actingAs(schedPanelUser('direccion'))->get('/panel/session-calendar')->assertOk();
    $this->actingAs(schedPanelUser('admin_tecnico'))->get('/panel/session-calendar')->assertForbidden();
});
