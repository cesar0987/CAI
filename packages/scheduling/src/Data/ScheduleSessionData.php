<?php

declare(strict_types=1);

namespace Cai\Scheduling\Data;

use Carbon\CarbonImmutable;

/**
 * @param  list<int>  $participants
 * @param  list<array{id: int, role: string}>  $professionals
 */
final readonly class ScheduleSessionData
{
    public function __construct(
        public int $serviceId,
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public array $participants = [],
        public array $professionals = [],
        public ?int $groupId = null,
        public string $sessionType = 'individual',
        public ?string $notes = null,
        public ?int $createdBy = null,
    ) {}
}
