<?php

declare(strict_types=1);

namespace Cai\Billing\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Card = 'card';
    case Deposit = 'deposit';
    case Check = 'check';
}
