<?php

declare(strict_types=1);

use Cai\Identity\Authorization\MultiRoleUser;
use Cai\Identity\Users\UserAccount;
use Cai\Identity\Users\UserRoleAssignment;
use Cai\Identity\Users\UserStatus;

it('supports a user with multiple active roles and checks combined membership rules', function () {
    $user = new UserAccount(
        9,
        'mix@cai.local',
        'Ana Mixta',
        UserStatus::ACTIVE,
        [
            'profesional' => new UserRoleAssignment('profesional', true),
            'recepcion' => new UserRoleAssignment('recepcion', true),
        ],
    );

    $multiRoleUser = new MultiRoleUser($user, ['coordinacion' => true]);

    expect($multiRoleUser->hasAnyRole('profesional', 'direccion'))->toBeTrue();
    expect($multiRoleUser->hasAnyRole('direccion', 'finanzas'))->toBeFalse();
    expect($multiRoleUser->hasAllRoles('profesional', 'recepcion'))->toBeTrue();
    expect($multiRoleUser->hasAllRoles('profesional', 'coordinacion'))->toBeTrue();
});
