<?php

declare(strict_types=1);

namespace Cai\Students\Enums;

enum StudentStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Withdrawn = 'withdrawn';
}
