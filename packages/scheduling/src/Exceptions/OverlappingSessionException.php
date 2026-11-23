<?php

declare(strict_types=1);

namespace Cai\Scheduling\Exceptions;

use Cai\Core\Exceptions\DomainException;

/**
 * Un profesional ya tiene una sesión en el rango solicitado.
 * En PostgreSQL la garantía dura es el EXCLUDE; este chequeo es la
 * optimización que da un mensaje accionable y cubre SQLite.
 */
final class OverlappingSessionException extends DomainException
{
    public static function code(): string
    {
        return 'SCHED-SESSION-OVERLAP';
    }
}
