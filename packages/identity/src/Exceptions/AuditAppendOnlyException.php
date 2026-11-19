<?php

declare(strict_types=1);

namespace Cai\Identity\Exceptions;

use Cai\Core\Exceptions\DomainException;

final class AuditAppendOnlyException extends DomainException
{
    public static function code(): string
    {
        return 'identity.audit_append_only';
    }
}
