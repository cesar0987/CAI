<?php

declare(strict_types=1);

namespace Cai\Scheduling\Enums;

/**
 * 03.04 §7. `excused` requiere justificación.
 */
enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case Excused = 'excused';
}
