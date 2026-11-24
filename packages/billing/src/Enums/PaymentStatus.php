<?php

declare(strict_types=1);

namespace Cai\Billing\Enums;

enum PaymentStatus: string
{
    case Confirmed = 'confirmed';
    case Voided = 'voided';
}
