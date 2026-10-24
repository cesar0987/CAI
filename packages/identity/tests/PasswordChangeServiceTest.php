<?php

declare(strict_types=1);

use Cai\Identity\PasswordPolicy\PasswordChangeCommand;
use Cai\Identity\PasswordPolicy\PasswordChangeService;
use Cai\Identity\Exceptions\PasswordChangeException;

it('allows a password change only when the current password matches and policy passes', function () {
    $service = new PasswordChangeService();

    $result = $service->changePassword(
        new PasswordChangeCommand('user@example.com', 'oldPass123', 'newPass456789'),
        true,
    );

    expect($result->isSuccess())->toBeTrue();
    expect($result->unwrap())->toBeTrue();
});

it('rejects invalid password change attempts', function () {
    $service = new PasswordChangeService();

    $result = $service->changePassword(
        new PasswordChangeCommand('user@example.com', 'oldPass123', 'short'),
        true,
    );

    expect($result->isFailure())->toBeTrue();
    expect($result->error())->toBeInstanceOf(PasswordChangeException::class);
});
