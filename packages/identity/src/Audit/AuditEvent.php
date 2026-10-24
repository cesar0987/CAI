<?php

declare(strict_types=1);

namespace Cai\Identity\Audit;

final readonly class AuditEvent
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public string $action,
        public string $entityType,
        public ?int $entityId,
        public array $context = [],
        public ?int $actorUserId = null,
        public ?int $studentId = null,
        public string $severity = 'info',
    ) {
    }
}
