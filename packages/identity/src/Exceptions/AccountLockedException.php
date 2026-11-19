<?php

declare(strict_types=1);

namespace Cai\Identity\Exceptions;

use Cai\Core\Exceptions\DomainException;

final class AccountLockedException extends DomainException
{
    public function __construct(string $message = 'La cuenta está bloqueada temporalmente.')
    {
        parent::__construct($message, 423);
    }

    public static function code(): string
    {
        return 'identity.account_locked';
    }
}
