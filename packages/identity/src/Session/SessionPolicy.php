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
        return $lastLoginAt <= $this->threshold($now, $this->absoluteTimeoutMinutes);
    }

    public function isIdleTimeout(\DateTimeInterface $lastSeenAt, \DateTimeInterface $now): bool
    {
        return $lastSeenAt <= $this->threshold($now, $this->idleTimeoutMinutes);
    }

    public function isExpired(\DateTimeInterface $lastSeenAt, \DateTimeInterface $lastLoginAt, \DateTimeInterface $now): bool
    {
        return $this->requiresRegeneration($lastLoginAt, $now) || $this->isIdleTimeout($lastSeenAt, $now);
    }

    /**
     * Calcula el límite sin mutar el instante recibido.
     */
    private function threshold(\DateTimeInterface $now, int $minutes): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromInterface($now)
            ->sub(new \DateInterval('PT'.$minutes.'M'));
    }
}
