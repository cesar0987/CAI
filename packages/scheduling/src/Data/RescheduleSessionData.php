<?php

declare(strict_types=1);

namespace Cai\Scheduling\Data;

use Carbon\CarbonImmutable;

final readonly class RescheduleSessionData
{
    public function __construct(
        public int $sessionId,
        public CarbonImmutable $newStartsAt,
        public CarbonImmutable $newEndsAt,
        public ?string $reason = null,
        public ?int $createdBy = null,
    ) {}
}
