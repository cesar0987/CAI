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

    expect(EffectivePermissions::has($user, 'care.notes.read'))->toBeTrue();
    expect(EffectivePermissions::has($user, 'notifications.send'))->toBeFalse();
    expect(array_key_exists('care.notes.read', EffectivePermissions::fromUser($user)))->toBeTrue();
});
