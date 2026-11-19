<?php

declare(strict_types=1);

namespace Cai\Identity\Exceptions;

use Cai\Core\Exceptions\DomainException;

final class AccountInactiveException extends DomainException
{
    public function __construct(string $message = 'La cuenta no está activa.')
    {
        parent::__construct($message, 403);
    }

    public static function code(): string
    {
        return 'identity.account_inactive';
    }
}
