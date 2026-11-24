<?php

declare(strict_types=1);

namespace Cai\Care\Data;

final readonly class RevokeClinicalNoteGrantData
{
    public function __construct(
        public int $grantId,
        public int $actorUserId,
        public string $reason,
    ) {}
}
