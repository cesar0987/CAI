<?php

declare(strict_types=1);

namespace Cai\Scheduling\Results;

final readonly class AttendanceSummary
{
    public function __construct(
        public int $sessionId,
        public int $recorded,
    ) {}
}
