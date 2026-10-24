<?php

declare(strict_types=1);

namespace Cai\Identity\Session;

final readonly class SessionPolicy
{
    public function __construct(
        public int $idleTimeoutMinutes = 30,
        public int $absoluteTimeoutMinutes = 480,
    ) {
        if ($this->idleTimeoutMinutes <= 0 || $this->absoluteTimeoutMinutes <= 0) {
            throw new \InvalidArgumentException('Los tiempos de sesión deben ser positivos.');
        }
    }

    public function requiresRegeneration(\DateTimeInterface $lastLoginAt, \DateTimeInterface $now): bool
    {
        return $lastLoginAt <= $now->modify('-'. $this->absoluteTimeoutMinutes.' minutes');
    }

    public function isIdleTimeout(\DateTimeInterface $lastSeenAt, \DateTimeInterface $now): bool
    {
        return $lastSeenAt <= $now->modify('-'. $this->idleTimeoutMinutes.' minutes');
    }

    public function isExpired(\DateTimeInterface $lastSeenAt, \DateTimeInterface $lastLoginAt, \DateTimeInterface $now): bool
    {
        return $this->requiresRegeneration($lastLoginAt, $now) || $this->isIdleTimeout($lastSeenAt, $now);
    }
}
