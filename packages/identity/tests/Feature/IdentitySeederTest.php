<?php

declare(strict_types=1);

use Cai\Identity\Models\FeatureFlag;
use Cai\Identity\Models\Permission;
use Cai\Identity\Models\Role;
use Database\Seeders\IdentitySeeder;

it('seeds the seven roles and 65 permissions of 04.02', function () {
    $this->seed(IdentitySeeder::class);

    expect(Role::count())->toBe(7)
        ->and(Permission::count())->toBe(65)
        ->and(Permission::where('is_sensitive', true)->count())->toBe(16);

    $direccion = Role::where('name', 'direccion')->first();
    expect($direccion->label)->toBe('Dirección')
        ->and($direccion->permissions)->toHaveCount(51);
});

it('is idempotent: seeding twice does not duplicate anything', function () {
    $this->seed(IdentitySeeder::class);
    $this->seed(IdentitySeeder::class);

    expect(Role::count())->toBe(7)
        ->and(Permission::count())->toBe(65)
        ->and(FeatureFlag::count())->toBe(7);

    $direccion = Role::where('name', 'direccion')->first();
    expect($direccion->permissions)->toHaveCount(51);
});

it('enforces the red lines in the seeded database', function () {
    $this->seed(IdentitySeeder::class);

    foreach (['admin_tecnico', 'finanzas'] as $roleName) {
        $role = Role::where('name', $roleName)->first();
        $carePermissions = $role->permissions->filter(
            fn (Permission $permission): bool => str_starts_with($permission->name, 'care.')
        );
        expect($carePermissions)->toHaveCount(0, "{$roleName} no puede tener permisos de care");
    }

    $profesional = Role::where('name', 'profesional')->first();
    $billingPermissions = $profesional->permissions->filter(
        fn (Permission $permission): bool => str_starts_with($permission->name, 'billing.')
    );
    expect($billingPermissions)->toHaveCount(0, 'profesional no puede tener permisos de billing');

    $recepcion = Role::where('name', 'recepcion')->first();
    expect($recepcion->hasPermissionTo('students.students.view'))->toBeFalse();

    $direccion = Role::where('name', 'direccion')->first();
    expect($direccion->hasPermissionTo('care.clinical.read'))->toBeFalse();
});

it('grants the conditional permissions as gates for the policies', function () {
    $this->seed(IdentitySeeder::class);

    // Los `*` de la matriz existen en el rol; la condición la verifica
    // la policy (04.03). Sin la puerta, ningún scope podría habilitarlos.
    $profesional = Role::where('name', 'profesional')->first();
    expect($profesional->hasPermissionTo('students.students.view'))->toBeTrue()
        ->and($profesional->hasPermissionTo('care.notes.view'))->toBeTrue()
        ->and($profesional->hasPermissionTo('care.notes.create'))->toBeTrue();

    $finanzas = Role::where('name', 'finanzas')->first();
    expect($finanzas->hasPermissionTo('billing.charges.adjust'))->toBeTrue()
        ->and($finanzas->hasPermissionTo('students.students.view'))->toBeTrue();
});

it('seeds feature flags without overwriting operator state on re-run', function () {
    $this->seed(IdentitySeeder::class);

    $flag = FeatureFlag::find('module.billing');
    expect($flag->is_enabled)->toBeFalse();

    // El operador apaga/enciende un flag en caliente (06.04)…
    $flag->update(['is_enabled' => true]);

    // …y un re-seed no debe pisar esa decisión.
    $this->seed(IdentitySeeder::class);

    expect(FeatureFlag::find('module.billing')->is_enabled)->toBeTrue();
});
