<?php

declare(strict_types=1);

namespace Cai\Identity\Exceptions;

use Cai\Core\Exceptions\DomainException;

final class PasswordChangeRequiredException extends DomainException
{
    public function __construct(string $message = 'Debe cambiar su contraseña.')
    {
        parent::__construct($message, 403);
    }

    public static function code(): string
    {
        return 'identity.password_change_required';
    }
}
