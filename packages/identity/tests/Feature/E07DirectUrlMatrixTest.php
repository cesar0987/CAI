<?php

declare(strict_types=1);

use Cai\Identity\Authorization\RoleCatalog;
use Cai\Identity\Models\User;
use Database\Seeders\IdentitySeeder;
use Illuminate\Support\Facades\DB;

/**
 * E-07 / entregable central del gate F2: ningún rol accede por URL directa
 * a un recurso para el que la matriz 04.02 no le da permiso, y todos los
 * que sí lo tienen pueden entrar.
 */
beforeEach(function () {
    $this->seed(IdentitySeeder::class);
});

it('matches the 04.02 matrix for every role on every resource URL', function () {
    $resources = [
        'users' => 'identity.users.view',
        'roles' => 'identity.roles.manage',
        'audit-events' => 'identity.audit.view',
        'security-events' => 'identity.security_events.view',
        'settings' => 'identity.settings.manage',
        'feature-flags' => 'identity.flags.manage',
    ];

    foreach (RoleCatalog::all() as $roleName => $role) {
        foreach ($resources as $resource => $permission) {
            $user = User::factory()->create(['password_changed_at' => now()]);
            $user->assignRole($roleName);

            $expectedStatus = $role->hasPermission($permission) ? 200 : 403;
            $response = $this->actingAs($user->fresh())->get('/panel/'.$resource);

            expect($response->getStatusCode())
                ->toBe($expectedStatus, "{$roleName} sobre /panel/{$resource} ({$permission})");
        }
    }
});

it('denies every resource to a user without roles', function () {
    $sinRol = User::factory()->create(['password_changed_at' => now()]);

    foreach (['users', 'roles', 'audit-events', 'security-events', 'settings', 'feature-flags'] as $resource) {
        $this->actingAs($sinRol)->get('/panel/'.$resource)->assertForbidden();
    }
});

it('gates write URLs with the manage permission', function () {
    $direccion = User::factory()->create(['password_changed_at' => now()]);
    $direccion->assignRole('direccion');
    $coordinacion = User::factory()->create(['password_changed_at' => now()]);
    $coordinacion->assignRole('coordinacion');

    $this->actingAs($direccion->fresh())->get('/panel/users/create')->assertOk();
    $this->actingAs($coordinacion->fresh())->get('/panel/users/create')->assertForbidden();
    $this->actingAs($coordinacion->fresh())->get('/panel/settings/create')->assertForbidden();
    $this->actingAs($direccion->fresh())->get('/panel/settings/create')->assertOk();
});

it('audits sensitive reads of audit and security events', function () {
    $direccion = User::factory()->create(['password_changed_at' => now()]);
    $direccion->assignRole('direccion');
    $direccion = $direccion->fresh();

    $this->actingAs($direccion)->get('/panel/audit-events')->assertOk();
    $this->actingAs($direccion)->get('/panel/security-events')->assertOk();

    expect(DB::table('audit_events')->where('action', 'audit.viewed')->count())->toBe(1)
        ->and(DB::table('audit_events')->where('action', 'security_events.viewed')->count())->toBe(1);
});
