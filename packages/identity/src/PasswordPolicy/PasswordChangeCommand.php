<?php

declare(strict_types=1);

namespace Cai\Identity\PasswordPolicy;

final readonly class PasswordChangeCommand
{
    public function __construct(
        public string $email,
        public string $currentPassword,
        public string $newPassword,
    ) {
        if (trim($email) === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('El email del cambio de contraseña no es válido.');
        }

        if (trim($currentPassword) === '') {
            throw new \InvalidArgumentException('La contraseña actual no puede estar vacía.');
        }

        if (trim($newPassword) === '') {
            throw new \InvalidArgumentException('La nueva contraseña no puede estar vacía.');
        }
    }
}
