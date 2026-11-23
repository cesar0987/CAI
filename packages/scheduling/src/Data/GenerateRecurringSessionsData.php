<?php

declare(strict_types=1);

namespace Cai\Scheduling\Data;

use Carbon\CarbonImmutable;

final readonly class GenerateRecurringSessionsData
{
    public function __construct(
        public int $serviceId,
        public CarbonImmutable $from,
        public CarbonImmutable $to,
        public ?int $createdBy = null,
    ) {}
}
