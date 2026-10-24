<?php

declare(strict_types=1);

use Cai\Identity\Session\SessionPolicy;

it('enforces idle and absolute timeout rules for a secure session', function () {
    $policy = new SessionPolicy(30, 480);

    $now = new DateTimeImmutable('2026-10-23 10:00:00');
    $lastSeen = new DateTimeImmutable('2026-10-23 09:25:00');
    $lastLogin = new DateTimeImmutable('2026-10-23 02:00:00');

    expect($policy->isIdleTimeout($lastSeen, $now))->toBeTrue();
    expect($policy->requiresRegeneration($lastLogin, $now))->toBeTrue();
    expect($policy->isExpired($lastSeen, $lastLogin, $now))->toBeTrue();

    $freshSeen = new DateTimeImmutable('2026-10-23 09:45:00');
    $freshLogin = new DateTimeImmutable('2026-10-23 09:50:00');

    expect($policy->isIdleTimeout($freshSeen, $now))->toBeFalse();
    expect($policy->requiresRegeneration($freshLogin, $now))->toBeFalse();
    expect($policy->isExpired($freshSeen, $freshLogin, $now))->toBeFalse();
});

it('rejects invalid session values', function () {
    new SessionPolicy(0, 480);
})->throws(InvalidArgumentException::class);
