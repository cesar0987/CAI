<?php

declare(strict_types=1);

use Cai\Identity\Exceptions\PasswordResetException;
use Cai\Identity\PasswordReset\PasswordResetRequest;
use Cai\Identity\PasswordReset\PasswordResetService;
use Cai\Identity\PasswordReset\PasswordResetToken;

it('resets a password when the token is valid and matches the user email', function () {
    $issuedAt = new DateTimeImmutable('now');
    $expiresAt = $issuedAt->modify('+60 minutes');

    $token = new PasswordResetToken('user@example.com', 'abc123', $issuedAt, $expiresAt, false);
    $service = new PasswordResetService;

    $result = $service->resetPassword(
        new PasswordResetRequest('user@example.com', 'abc123', 'NewPassword123'),
        $token,
        true,
    );

    expect($result->isSuccess())->toBeTrue();
    expect($result->unwrap())->toBeTrue();
});

it('rejects invalid or mismatched reset attempts', function () {
    $issuedAt = new DateTimeImmutable('now');
    $expiresAt = $issuedAt->modify('+60 minutes');

    $service = new PasswordResetService;
    $token = new PasswordResetToken('user@example.com', 'abc123', $issuedAt, $expiresAt, false);

    $result = $service->resetPassword(
        new PasswordResetRequest('other@example.com', 'abc123', 'NewPassword123'),
        $token,
        true,
    );

    expect($result->isFailure())->toBeTrue();
    expect($result->error())->toBeInstanceOf(PasswordResetException::class);
});
