<?php

declare(strict_types=1);

use Cai\Identity\Users\UserAccount;
use Cai\Identity\Users\UserRoleAssignment;
use Cai\Identity\Users\UserStatus;

it('models a user account with status and role assignments', function () {
    $account = new UserAccount(
        12,
        'admin@cai.local',
        'Ana Admin',
        UserStatus::ACTIVE,
        [
            'direccion' => new UserRoleAssignment('direccion', true),
            'admin_tecnico' => new UserRoleAssignment('admin_tecnico', false),
        ],
    );

    expect($account->isActive())->toBeTrue();
    expect($account->hasRole('direccion'))->toBeTrue();
    expect($account->hasRole('admin_tecnico'))->toBeFalse();
    expect($account->status)->toBe(UserStatus::ACTIVE);
});

it('restricts access to assigned students unless the user is a privileged role', function () {
    $professional = new UserAccount(
        15,
        'profesional@cai.local',
        'Dr. Ruiz',
        UserStatus::ACTIVE,
        ['profesional' => new UserRoleAssignment('profesional', true)],
        [10, 11],
    );

    $director = new UserAccount(
        10,
        'direccion@cai.local',
        'Dirección',
        UserStatus::ACTIVE,
        ['direccion' => new UserRoleAssignment('direccion', true)],
    );

    expect($professional->canAccessStudent(10))->toBeTrue();
    expect($professional->canAccessStudent(99))->toBeFalse();
    expect($director->canAccessStudent(99))->toBeTrue();
});

it('rejects empty user identity fields', function () {
    new UserAccount(1, '', 'Ana', UserStatus::ACTIVE);
})->throws(InvalidArgumentException::class);
