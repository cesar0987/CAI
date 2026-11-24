<?php

declare(strict_types=1);

namespace Cai\Care\Data;

/** Una corrección siempre crea una nota nueva con motivo ≥ 20 caracteres. */
final readonly class SupersedeClinicalNoteData
{
    public function __construct(
        public int $noteId,
        public int $actorUserId,
        public string $newContent,
        public string $correctionReason,
        public ?string $newSummary = null,
    ) {}
}
