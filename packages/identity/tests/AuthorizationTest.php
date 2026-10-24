<?php

declare(strict_types=1);

use Cai\Identity\Authorization\Permission;
use Cai\Identity\Authorization\Role;

it('defines a permission with module and sensitivity metadata', function () {
    $permission = new Permission('students.read', 'students', true, 'Leer alumnos');

    expect($permission->name)->toBe('students.read');
    expect($permission->module)->toBe('students');
    expect($permission->sensitive)->toBeTrue();
    expect($permission->matches('students.read'))->toBeTrue();
});

it('allows a role to check permissions by name', function () {
    $studentsRead = new Permission('students.read', 'students');
    $role = new Role('director', ['students.read' => $studentsRead]);

    expect($role->hasPermission('students.read'))->toBeTrue();
    expect($role->hasPermission('students.write'))->toBeFalse();
});
