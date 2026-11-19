<?php

declare(strict_types=1);

use Cai\Identity\Models\AuditEvent;
use Cai\Identity\Models\FeatureFlag;
use Cai\Identity\Models\Permission;
use Cai\Identity\Models\Role;
use Cai\Identity\Models\SecurityEvent;
use Cai\Identity\Models\Setting;
use Cai\Identity\Models\User;
use Database\Seeders\IdentitySeeder;

/**
 * Tests por rama de las policies de identity (04.03 §5): permiso primero,
 * negación temprana, denegación por defecto para usuarios sin rol.
 * Los roles y permisos vienen del seeder canónico (04.02).
 */
beforeEach(function () {
    $this->seed(IdentitySeeder::class);
});

function userWithRole(string $roleName): User
{
    $user = User::factory()->create(['password_changed_at' => now()]);
    $user->assignRole($roleName);

    return $user->fresh();
}

describe('UserPolicy', function () {
    it('allows viewAny only with identity.users.view', function () {
        expect(userWithRole('direccion')->can('viewAny', User::class))->toBeTrue()
            ->and(userWithRole('coordinacion')->can('viewAny', User::class))->toBeTrue()
            ->and(userWithRole('admin_tecnico')->can('viewAny', User::class))->toBeTrue()
            ->and(userWithRole('secretaria')->can('viewAny', User::class))->toBeFalse()
            ->and(userWithRole('profesional')->can('viewAny', User::class))->toBeFalse();
    });

    it('always allows viewing and updating own profile', function () {
        $user = userWithRole('profesional');

        expect($user->can('view', $user))->toBeTrue()
            ->and($user->can('update', $user))->toBeTrue();
    });

    it('requires manage to create or update other users', function () {
        $target = User::factory()->create();

        expect(userWithRole('direccion')->can('create', User::class))->toBeTrue()
            ->and(userWithRole('admin_tecnico')->can('update', $target))->toBeTrue()
            ->and(userWithRole('coordinacion')->can('update', $target))->toBeFalse()
            ->and(userWithRole('secretaria')->can('create', User::class))->toBeFalse();
    });

    it('never allows deleting users, not even for direccion', function () {
        $target = User::factory()->create();
        $direccion = userWithRole('direccion');

        expect($direccion->can('delete', $target))->toBeFalse()
            ->and($direccion->can('forceDelete', $target))->toBeFalse()
            ->and($direccion->can('restore', $target))->toBeFalse();
    });
});

describe('RolePolicy', function () {
    it('is exclusive to direccion (admin_tecnico has no roles.manage)', function () {
        $role = Role::where('name', 'secretaria')->first();

        expect(userWithRole('direccion')->can('viewAny', Role::class))->toBeTrue()
            ->and(userWithRole('direccion')->can('update', $role))->toBeTrue()
            ->and(userWithRole('admin_tecnico')->can('viewAny', Role::class))->toBeFalse()
            ->and(userWithRole('admin_tecnico')->can('update', $role))->toBeFalse();
    });

    it('never allows deleting canonical roles', function () {
        $role = Role::where('name', 'direccion')->first();

        expect(userWithRole('direccion')->can('delete', $role))->toBeFalse();
    });
});

describe('PermissionPolicy', function () {
    it('is read-only for roles with identity.permissions.view', function () {
        $permission = Permission::where('name', 'identity.users.view')->first();

        expect(userWithRole('direccion')->can('viewAny', Permission::class))->toBeTrue()
            ->and(userWithRole('admin_tecnico')->can('view', $permission))->toBeTrue()
            ->and(userWithRole('coordinacion')->can('viewAny', Permission::class))->toBeFalse()
            ->and(userWithRole('direccion')->can('update', $permission))->toBeFalse()
            ->and(userWithRole('direccion')->can('create', Permission::class))->toBeFalse()
            ->and(userWithRole('direccion')->can('delete', $permission))->toBeFalse();
    });
});

describe('AuditEventPolicy', function () {
    it('allows reading only to direccion, coordinacion and admin_tecnico', function () {
        $event = AuditEvent::create([
            'action' => 'user.created',
            'entity_type' => User::class,
            'entity_id' => '1',
        ]);

        foreach (['direccion', 'coordinacion', 'admin_tecnico'] as $roleName) {
            expect(userWithRole($roleName)->can('viewAny', AuditEvent::class))->toBeTrue();
            expect(userWithRole($roleName)->can('view', $event))->toBeTrue();
        }

        foreach (['secretaria', 'profesional', 'finanzas', 'recepcion'] as $roleName) {
            expect(userWithRole($roleName)->can('viewAny', AuditEvent::class))->toBeFalse();
            expect(userWithRole($roleName)->can('view', $event))->toBeFalse();
        }
    });

    it('never allows writing audit events, not even for direccion', function () {
        $event = AuditEvent::create([
            'action' => 'user.created',
            'entity_type' => User::class,
            'entity_id' => '1',
        ]);
        $direccion = userWithRole('direccion');

        expect($direccion->can('create', AuditEvent::class))->toBeFalse()
            ->and($direccion->can('update', $event))->toBeFalse()
            ->and($direccion->can('delete', $event))->toBeFalse()
            ->and($direccion->can('forceDelete', $event))->toBeFalse();
    });
});

describe('SecurityEventPolicy', function () {
    it('allows reading only to direccion and admin_tecnico', function () {
        $event = SecurityEvent::create([
            'event_type' => 'access.denied',
            'severity' => 'warning',
        ]);

        expect(userWithRole('direccion')->can('view', $event))->toBeTrue()
            ->and(userWithRole('admin_tecnico')->can('viewAny', SecurityEvent::class))->toBeTrue()
            ->and(userWithRole('coordinacion')->can('viewAny', SecurityEvent::class))->toBeFalse()
            ->and(userWithRole('secretaria')->can('view', $event))->toBeFalse();
    });

    it('never allows writing security events', function () {
        $event = SecurityEvent::create(['event_type' => 'access.denied', 'severity' => 'warning']);

        expect(userWithRole('direccion')->can('update', $event))->toBeFalse()
            ->and(userWithRole('direccion')->can('delete', $event))->toBeFalse();
    });
});

describe('SettingPolicy', function () {
    it('requires identity.settings.manage for everything', function () {
        $setting = Setting::create([
            'key' => 'centro.nombre',
            'value' => 'CAI',
            'type' => 'string',
            'group_name' => 'centro',
        ]);

        expect(userWithRole('direccion')->can('viewAny', Setting::class))->toBeTrue()
            ->and(userWithRole('admin_tecnico')->can('update', $setting))->toBeTrue()
            ->and(userWithRole('coordinacion')->can('viewAny', Setting::class))->toBeFalse()
            ->and(userWithRole('secretaria')->can('update', $setting))->toBeFalse()
            ->and(userWithRole('direccion')->can('delete', $setting))->toBeFalse();
    });
});

describe('FeatureFlagPolicy', function () {
    it('requires identity.flags.manage to view and toggle', function () {
        $flag = FeatureFlag::find('module.billing');

        expect(userWithRole('direccion')->can('viewAny', FeatureFlag::class))->toBeTrue()
            ->and(userWithRole('admin_tecnico')->can('update', $flag))->toBeTrue()
            ->and(userWithRole('coordinacion')->can('update', $flag))->toBeFalse()
            ->and(userWithRole('finanzas')->can('viewAny', FeatureFlag::class))->toBeFalse()
            ->and(userWithRole('direccion')->can('create', FeatureFlag::class))->toBeFalse()
            ->and(userWithRole('direccion')->can('delete', $flag))->toBeFalse();
    });
});

it('denies everything by default to users without roles', function () {
    $sinRol = User::factory()->create(['password_changed_at' => now()]);
    $target = User::factory()->create();
    $event = AuditEvent::create(['action' => 'x', 'entity_type' => User::class, 'entity_id' => '1']);

    expect($sinRol->can('viewAny', User::class))->toBeFalse()
        ->and($sinRol->can('view', $target))->toBeFalse()
        ->and($sinRol->can('viewAny', AuditEvent::class))->toBeFalse()
        ->and($sinRol->can('view', $event))->toBeFalse()
        ->and($sinRol->can('viewAny', Role::class))->toBeFalse()
        ->and($sinRol->can('viewAny', Setting::class))->toBeFalse()
        ->and($sinRol->can('viewAny', FeatureFlag::class))->toBeFalse();
});

it('denies everything to inactive accounts even with permissions', function () {
    $inactive = User::factory()->create(['status' => 'inactive', 'password_changed_at' => now()]);
    $inactive->assignRole('direccion');
    $inactive = $inactive->fresh();

    expect($inactive->can('viewAny', User::class))->toBeFalse()
        ->and($inactive->can('viewAny', AuditEvent::class))->toBeFalse()
        ->and($inactive->can('viewAny', Role::class))->toBeFalse();
});
