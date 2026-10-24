<?php

declare(strict_types=1);

use Cai\Identity\Login\LoginAttemptPolicy;

it('blocks after the maximum number of failed attempts within the configured window', function () {
    $policy = new LoginAttemptPolicy(3, 15);
    $now = new DateTimeImmutable('2026-10-23 09:00:00');

    $policy->registerFailure('user@example.com', '127.0.0.1', $now);
    $policy->registerFailure('user@example.com', '127.0.0.1', $now->modify('+2 minutes'));

    expect($policy->isBlocked('user@example.com', '127.0.0.1', $now->modify('+5 minutes')))->toBeFalse();

    $policy->registerFailure('user@example.com', '127.0.0.1', $now->modify('+4 minutes'));

    expect($policy->isBlocked('user@example.com', '127.0.0.1', $now->modify('+5 minutes')))->toBeTrue();

    $policy->registerSuccess('user@example.com', '127.0.0.1');

    expect($policy->isBlocked('user@example.com', '127.0.0.1', $now->modify('+6 minutes')))->toBeFalse();
});

it('rejects invalid limits', function () {
    new LoginAttemptPolicy(0, 15);
})->throws(InvalidArgumentException::class);
