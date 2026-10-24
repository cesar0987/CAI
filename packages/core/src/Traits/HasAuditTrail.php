<?php

declare(strict_types=1);

namespace Cai\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait HasAuditTrail
{
    protected static function bootHasAuditTrail(): void
    {
        static::created(function (Model $model): void {
            $model->recordAudit('created');
        });

        static::updated(function (Model $model): void {
            $model->recordAudit('updated', $model->getDirty());
        });

        static::deleted(function (Model $model): void {
            $model->recordAudit('deleted', $model->getAttributes());
        });
    }

    public function recordAudit(string $event, array $changes = []): void
    {
        if (! class_exists(DB::class)) {
            return;
        }

        $connection = DB::connection();

        if (! $connection) {
            return;
        }

        $table = static::auditTableName();

        $payload = [
            'event' => $event,
            'model' => static::class,
            'model_id' => $this->getKey(),
            'changes' => json_encode($changes, JSON_THROW_ON_ERROR),
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];

        if ($this->getKey() === null) {
            $payload['model_id'] = null;
        }

        $connection->table($table)->insert($payload);
    }

    public static function auditTableName(): string
    {
        return 'audit_events';
    }
}
