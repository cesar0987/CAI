<?php

declare(strict_types=1);

namespace Cai\Care\Data;

final readonly class IssueClinicalNoteGrantData
{
    public function __construct(
        public int $actorUserId,
        public int $grantedToUserId,
        public int $studentId,
        public string $reason,
        public ?int $clinicalNoteId = null,
        public int $days = 7,
    ) {}
}
