<?php

declare(strict_types=1);

namespace Cai\Care\Data;

/** Datos para abrir una nota en borrador (08.07 §2.3). */
final readonly class CreateClinicalNoteData
{
    public function __construct(
        public int $actorUserId,
        public int $studentId,
        public string $noteType,
        public string $content,
        public ?string $summary = null,
        public ?int $sessionId = null,
        public ?int $carePlanId = null,
    ) {}
}
