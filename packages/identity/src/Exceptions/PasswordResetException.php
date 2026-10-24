<?php

declare(strict_types=1);

namespace Cai\Identity\Exceptions;

use Cai\Core\Exceptions\DomainException;

final class PasswordResetException extends DomainException
{
    public function __construct(string $message = 'La recuperación de contraseña no es válida.')
    {
        parent::__construct($message, 400);
    }

    public static function code(): string
    {
        return 'identity.password_reset_invalid';
    }
}
