<?php

declare(strict_types=1);

namespace Cai\Care\Data;

final readonly class FinalizeClinicalNoteData
{
    public function __construct(
        public int $noteId,
        public int $actorUserId,
    ) {}
}
