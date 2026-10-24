<?php

declare(strict_types=1);

namespace Cai\Identity\PasswordReset;

final readonly class PasswordResetRequest
{
    public function __construct(
        public string $email,
        public string $token,
        public string $newPassword,
    ) {
        if (trim($email) === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('El email de recuperación no es válido.');
        }

        if (trim($token) === '') {
            throw new \InvalidArgumentException('El token de recuperación no puede estar vacío.');
        }

        if (strlen($newPassword) < 12) {
            throw new \InvalidArgumentException('La nueva contraseña debe tener al menos 12 caracteres.');
        }
    }
}
