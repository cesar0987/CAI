<?php

declare(strict_types=1);

namespace Cai\Billing\Queries;

use Cai\Billing\Support\Money;

final readonly class FamilyAccountBalanceRow
{
    public function __construct(
        public string $charged,
        public string $allocated,
        public string $outstanding,
    ) {}

    public function outstandingMoney(): Money
    {
        return Money::parse($this->outstanding);
    }
}
