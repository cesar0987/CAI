<?php

declare(strict_types=1);

namespace Cai\Scheduling\Exceptions;

use Cai\Core\Exceptions\DomainException;

final class ParticipantNotFoundException extends DomainException
{
    public static function code(): string
    {
        return 'SCHED-PARTICIPANT-NOT-FOUND';
    }
}
