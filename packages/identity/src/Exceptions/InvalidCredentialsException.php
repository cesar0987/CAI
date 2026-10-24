<?php

declare(strict_types=1);

namespace Cai\Identity\Exceptions;

use Cai\Core\Exceptions\DomainException;

final class InvalidCredentialsException extends DomainException
{
    public function __construct(string $message = 'Las credenciales no son correctas.')
    {
        parent::__construct($message, 401);
    }

    public static function code(): string
    {
        return 'identity.invalid_credentials';
    }
}
