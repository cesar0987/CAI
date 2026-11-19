<?php

declare(strict_types=1);

namespace Cai\Identity\Audit;

use Illuminate\Support\Facades\DB;

final class Auditor
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(
        string $action,
        string $entityType,
        ?int $entityId,
        array $context = [],
        ?int $actorUserId = null,
        ?int $studentId = null,
        string $severity = 'info',
    ): void {
        $connection = DB::connection();

        // En PostgreSQL la tabla vive en el schema identity; en SQLite
        // (desarrollo y tests) los nombres van sin cualificar.
        $table = $connection->getDriverName() === 'pgsql'
            ? 'identity.audit_events'
            : 'audit_events';

        $connection->table($table)->insert([
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'context' => json_encode($context, JSON_THROW_ON_ERROR),
            'actor_user_id' => $actorUserId,
            'student_id' => $studentId,
            'severity' => $severity,
            'created_at' => now()->toDateTimeString(),
        ]);
    }
}
