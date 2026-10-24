<?php

declare(strict_types=1);

use Cai\Identity\PasswordReset\PasswordResetToken;

it('creates a valid token that expires after 60 minutes and can be consumed once', function () {
    $issuedAt = new DateTimeImmutable('2026-10-23 08:00:00');
    $expiresAt = $issuedAt->modify('+60 minutes');

    $token = new PasswordResetToken('user@example.com', 'abc123', $issuedAt, $expiresAt, false);

    expect($token->isUsable($issuedAt))->toBeTrue();
    expect($token->isExpired($issuedAt))->toBeFalse();

    $consumed = $token->consume();

    expect($consumed->used)->toBeTrue();
    expect($consumed->isUsable($issuedAt))->toBeFalse();
});

it('rejects invalid email or expired tokens', function () {
    new PasswordResetToken('bad-email', 'abc123');
})->throws(InvalidArgumentException::class);
