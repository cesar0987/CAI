<?php

declare(strict_types=1);

namespace Cai\Billing\Support;

/** Saldo derivado de un cargo; nunca se almacena (03.06 §2). */
final readonly class ChargeBalance
{
    public function __construct(
        public string $charged,
        public string $allocated,
        public string $outstanding,
    ) {}

    public function isSettled(): bool
    {
        return bccomp($this->outstanding, '0', 2) === 0;
    }

    public function isPartial(): bool
    {
        return bccomp($this->allocated, '0', 2) > 0 && bccomp($this->outstanding, '0', 2) > 0;
    }
}
