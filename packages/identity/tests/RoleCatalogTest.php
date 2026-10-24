<?php

declare(strict_types=1);

use Cai\Identity\Authorization\RoleCatalog;

it('exposes the domain roles from the canonical catalog', function () {
    $roles = RoleCatalog::all();

    expect(array_keys($roles))->toContain('direccion');
    expect(array_keys($roles))->toContain('coordinacion');
    expect(array_keys($roles))->toContain('secretaria');
    expect(array_keys($roles))->toContain('profesional');
    expect(array_keys($roles))->toContain('finanzas');
    expect(array_keys($roles))->toContain('recepcion');
    expect(array_keys($roles))->toContain('admin_tecnico');
    expect($roles['direccion']->hasPermission('care.notes.read'))->toBeTrue();
    expect($roles['finanzas']->hasPermission('care.notes.read'))->toBeFalse();
});
