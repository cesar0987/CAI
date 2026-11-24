<?php

declare(strict_types=1);

namespace Cai\Billing\Enums;

/** 03.06 §4: el estado se recalcula al imputar o anular. */
enum ChargeStatus: string
{
    case Pending = 'pending';
    case Partial = 'partial';
    case Paid = 'paid';
    case Voided = 'voided';
}
