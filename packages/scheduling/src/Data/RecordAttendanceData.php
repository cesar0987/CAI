<?php

declare(strict_types=1);

namespace Cai\Scheduling\Data;

/**
 * @param  list<array{participant_student_id: int, status: string, justification?: string|null}>  $records
 */
final readonly class RecordAttendanceData
{
    public function __construct(
        public int $sessionId,
        public array $records,
        public int $recordedBy,
    ) {}
}
