<?php

declare(strict_types=1);

namespace Cai\Scheduling\Exceptions;

use Cai\Core\Exceptions\DomainException;

final class CapacityExceededException extends DomainException
{
    public static function code(): string
    {
        return 'SCHED-GROUP-CAPACITY';
    }
}
