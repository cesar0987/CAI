<?php

declare(strict_types=1);

namespace Cai\Billing\Enums;

enum CashSessionStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
}
