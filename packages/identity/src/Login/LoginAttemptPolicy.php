<?php

declare(strict_types=1);

namespace Cai\Identity\Login;

final class LoginAttemptPolicy
{
    /**
     * @var array<string, array<int, \DateTimeImmutable>>
     */
    private array $attempts = [];

    public function __construct(
        private readonly int $maxAttempts = 5,
        private readonly int $windowMinutes = 15,
    ) {
        if ($this->maxAttempts <= 0 || $this->windowMinutes <= 0) {
            throw new \InvalidArgumentException('Los límites de intentos deben ser positivos.');
        }
    }

    public function registerFailure(string $email, string $ipAddress, \DateTimeImmutable $at = new \DateTimeImmutable()): void
    {
        $key = $this->normalizeKey($email, $ipAddress);

        $this->attempts[$key] ??= [];
        $this->attempts[$key][] = $at;

        $this->pruneExpired($key, $at);
    }

    public function registerSuccess(string $email, string $ipAddress): void
    {
        $key = $this->normalizeKey($email, $ipAddress);

        unset($this->attempts[$key]);
    }

    public function isBlocked(string $email, string $ipAddress, \DateTimeImmutable $at = new \DateTimeImmutable()): bool
    {
        $key = $this->normalizeKey($email, $ipAddress);

        if (! isset($this->attempts[$key])) {
            return false;
        }

        $this->pruneExpired($key, $at);

        return count($this->attempts[$key] ?? []) >= $this->maxAttempts;
    }

    private function normalizeKey(string $email, string $ipAddress): string
    {
        return strtolower(trim($email)).'|'.trim($ipAddress);
    }

    private function pruneExpired(string $key, \DateTimeImmutable $at): void
    {
        $cutoff = $at->modify('-'.$this->windowMinutes.' minutes');

        $this->attempts[$key] = array_values(array_filter(
            $this->attempts[$key] ?? [],
            static fn (\DateTimeImmutable $attempt): bool => $attempt > $cutoff,
        ));

        if ($this->attempts[$key] === []) {
            unset($this->attempts[$key]);
        }
    }
}
