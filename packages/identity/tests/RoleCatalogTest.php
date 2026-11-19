<?php

declare(strict_types=1);

use Cai\Identity\Authorization\PermissionCatalog;
use Cai\Identity\Authorization\RoleCatalog;

it('exposes the seven domain roles from 04.02 with their labels', function () {
    $roles = RoleCatalog::all();

    expect(array_keys($roles))->toBe([
        'direccion', 'coordinacion', 'secretaria', 'profesional',
        'finanzas', 'recepcion', 'admin_tecnico',
    ]);
    expect($roles['direccion']->label)->toBe('Dirección');
    expect($roles['admin_tecnico']->label)->toBe('Administración Técnica');
});

it('matches the master matrix of 04.02 for every role', function () {
    $roles = RoleCatalog::all();

    // Dirección: identidad completa, pero care.clinical.read NO se concede
    // por rol (04.02 §3): solo por grant excepcional.
    expect($roles['direccion']->hasPermission('identity.users.manage'))->toBeTrue();
    expect($roles['direccion']->hasPermission('identity.roles.manage'))->toBeTrue();
    expect($roles['direccion']->hasPermission('care.clinical.read'))->toBeFalse();
    expect($roles['direccion']->hasPermission('care.notes.view'))->toBeFalse();
    expect($roles['direccion']->hasPermission('care.grants.issue'))->toBeTrue();
    expect($roles['direccion']->hasPermission('billing.payments.create'))->toBeFalse();

    // Coordinación: clínico condicionado y notificaciones sí
    expect($roles['coordinacion']->hasPermission('care.notes.view'))->toBeTrue();
    expect($roles['coordinacion']->hasPermission('notifications.messages.send'))->toBeTrue();
    expect($roles['coordinacion']->hasPermission('billing.charges.view'))->toBeFalse();

    // Secretaría: alumnos y agenda, sin clínico ni finanzas
    expect($roles['secretaria']->hasPermission('students.students.create'))->toBeTrue();
    expect($roles['secretaria']->hasPermission('care.notes.view'))->toBeFalse();
    expect($roles['secretaria']->hasPermission('billing.charges.view'))->toBeFalse();

    // Profesional: notas propias, sin finanzas
    expect($roles['profesional']->hasPermission('care.notes.create'))->toBeTrue();
    expect($roles['profesional']->hasPermission('care.notes.finalize'))->toBeTrue();
    expect($roles['profesional']->hasPermission('identity.users.view'))->toBeFalse();

    // Recepción: mínimo necesario, nunca students.students.view (04.02 §6)
    expect($roles['recepcion']->hasPermission('students.students.view'))->toBeFalse();
    expect($roles['recepcion']->hasPermission('students.authorized_pickups.manage'))->toBeTrue();

    // Admin técnico: identidad sin gestión de roles
    expect($roles['admin_tecnico']->hasPermission('identity.users.manage'))->toBeTrue();
    expect($roles['admin_tecnico']->hasPermission('identity.roles.manage'))->toBeFalse();
});

it('enforces the three red lines of 04.02 section 4', function () {
    $roles = RoleCatalog::all();

    foreach (['admin_tecnico', 'finanzas'] as $roleName) {
        foreach (PermissionCatalog::all() as $permission) {
            if ($permission->module === 'care') {
                expect($roles[$roleName]->hasPermission($permission->name))
                    ->toBeFalse("{$roleName} no puede tener {$permission->name}");
            }
        }
    }

    foreach (PermissionCatalog::all() as $permission) {
        if ($permission->module === 'billing') {
            expect($roles['profesional']->hasPermission($permission->name))
                ->toBeFalse("profesional no puede tener {$permission->name}");
        }
    }
});

it('marks exactly the sensitive permissions of 04.02 section 5', function () {
    $sensitive = array_keys(PermissionCatalog::sensitive());

    sort($sensitive);

    $expected = [
        'billing.charges.adjust', 'billing.export', 'billing.payments.create',
        'billing.payments.void', 'billing.receipts.view', 'billing.reports.view',
        'care.clinical.read', 'care.documents.view', 'care.grants.issue',
        'care.notes.create', 'care.notes.finalize', 'care.notes.view',
        'identity.flags.manage', 'identity.roles.manage', 'identity.users.manage',
        'students.family_accounts.manage',
    ];
    sort($expected);

    expect($sensitive)->toBe($expected);
});

it('defines the 65 permissions of the master matrix', function () {
    expect(PermissionCatalog::all())->toHaveCount(65);

    foreach (PermissionCatalog::all() as $permission) {
        expect($permission->name)->toStartWith($permission->module.'.');
    }
});
