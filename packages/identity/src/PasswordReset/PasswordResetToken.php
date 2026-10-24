<?php

declare(strict_types=1);

namespace Cai\Identity\PasswordReset;

final readonly class PasswordResetToken
{
    public function __construct(
        public string $email,
        public string $token,
        public \DateTimeImmutable $issuedAt = new \DateTimeImmutable(),
        public \DateTimeImmutable $expiresAt = new \DateTimeImmutable('+60 minutes'),
        public bool $used = false,
    ) {
        if (trim($email) === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('El email del token de recuperación no es válido.');
        }

        if (trim($token) === '') {
            throw new \InvalidArgumentException('El token de recuperación no puede estar vacío.');
        }

        if ($expiresAt <= $issuedAt) {
            throw new \InvalidArgumentException('La expiración del token debe estar en el futuro.');
        }
    }

    public function isExpired(\DateTimeInterface $at = new \DateTimeImmutable()): bool
    {
        return $at >= $this->expiresAt;
    }

    public function isUsable(\DateTimeInterface $at = new \DateTimeImmutable()): bool
    {
        return ! $this->used && ! $this->isExpired($at);
    }

    public function consume(): self
    {
        if ($this->used) {
            throw new \RuntimeException('El token de recuperación ya fue consumido.');
        }

        return new self(
            $this->email,
            $this->token,
            $this->issuedAt,
            $this->expiresAt,
            true,
        );
    }
}
