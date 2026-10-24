<?php

declare(strict_types=1);

namespace Cai\Identity\Exceptions;

use Cai\Core\Exceptions\DomainException;

final class PasswordChangeException extends DomainException
{
    public function __construct(string $message = 'La contraseña no puede cambiarse con esos datos.')
    {
        parent::__construct($message, 400);
    }

    public static function code(): string
    {
        return 'identity.password_change_invalid';
    }
}
