<?php

declare(strict_types=1);

namespace Cai\Scheduling\Enums;

enum SessionType: string
{
    case Individual = 'individual';
    case Group = 'group';
}
