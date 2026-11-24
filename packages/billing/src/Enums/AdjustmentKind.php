<?php

declare(strict_types=1);

namespace Cai\Billing\Enums;

enum AdjustmentKind: string
{
    case Discount = 'discount';
    case Scholarship = 'scholarship';
    case Correction = 'correction';
    case Waiver = 'waiver';
    case Surcharge = 'surcharge';
}
