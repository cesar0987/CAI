<?php

declare(strict_types=1);

use Cai\Identity\Authorization\ImpersonationPolicy;
use Cai\Identity\Users\UserAccount;
use Cai\Identity\Users\UserRoleAssignment;
use Cai\Identity\Users\UserStatus;

it('allows impersonation only for privileged actors and denies self or inactive targets', function () {
    $director = new UserAccount(
        1,
        'direccion@cai.local',
        'Dirección',
        UserStatus::ACTIVE,
        ['direccion' => new UserRoleAssignment('direccion', true)],
    );

    $assistant = new UserAccount(
        2,
        'profesional@cai.local',
        'Profesional',
        UserStatus::ACTIVE,
        ['profesional' => new UserRoleAssignment('profesional', true)],
    );

    $inactive = new UserAccount(
        3,
        'inactivo@cai.local',
        'Inactivo',
        UserStatus::INACTIVE,
        ['direccion' => new UserRoleAssignment('direccion', true)],
    );

    $policy = new ImpersonationPolicy();

    expect($policy->canImpersonate($director, $assistant))->toBeTrue();
    expect($policy->canImpersonate($assistant, $director))->toBeFalse();
    expect($policy->canImpersonate($director, $director))->toBeFalse();
    expect($policy->canImpersonate($director, $inactive))->toBeFalse();
});
