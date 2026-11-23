<?php

declare(strict_types=1);

namespace Cai\Scheduling\Enums;

/**
 * 03.04 §8: scheduled → completed / cancelled / rescheduled.
 * No hay `in_progress`: el estado se deriva de la hora y de la asistencia.
 */
enum SessionStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Rescheduled = 'rescheduled';
}
