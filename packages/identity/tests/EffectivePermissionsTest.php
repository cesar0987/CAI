<?php

declare(strict_types=1);

use Cai\Identity\Authorization\EffectivePermissions;
use Cai\Identity\Users\UserAccount;
use Cai\Identity\Users\UserRoleAssignment;
use Cai\Identity\Users\UserStatus;

it('derives the effective permissions from the user role assignments', function () {
    $user = new UserAccount(
        8,
        'coordinador@cai.local',
        'Coordinador',
        UserStatus::ACTIVE,
        [
            'coordinacion' => new UserRoleAssignment('coordinacion', true),
            'secretaria' => new UserRoleAssignment('secretaria', false),
        ],
    );

    // La puerta de entrada del rol clínico existe; la condición (rol clínico
    // asignado por Dirección) la verifica la policy, no el catálogo (04.02 §3).
    expect(EffectivePermissions::has($user, 'care.notes.view'))->toBeTrue();
    expect(EffectivePermissions::has($user, 'billing.charges.view'))->toBeFalse();
    expect(array_key_exists('care.notes.view', EffectivePermissions::fromUser($user)))->toBeTrue();
});
