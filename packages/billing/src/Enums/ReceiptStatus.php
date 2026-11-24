<?php

declare(strict_types=1);

namespace Cai\Billing\Enums;

enum ReceiptStatus: string
{
    case Issued = 'issued';
    case Voided = 'voided';
}
