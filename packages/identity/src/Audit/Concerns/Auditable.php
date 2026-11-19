<?php

declare(strict_types=1);

namespace Cai\Identity\Audit\Concerns;

use Cai\Identity\Audit\Auditor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Auditable — E-04 de F2 (08.04 §2.4, 04.05).
 *
 * Registra creación, modificación y borrado de un modelo. Desviación del
 * snippet del diseño: vive en identity y no en core porque el Auditor real
 * está en identity (Core no puede depender de Identity, Deptrac).
 *
 * Los campos secretos NUNCA se escriben en audit_events: se sustituyen por
 * '[redacted]'. La auditoría es append-only y no debe filtrar credenciales.
 */
trait Auditable
{
    /**
     * @var list<string>
     */
    protected static array $auditRedactedFields = [
        'password', 'remember_token', 'mfa_secret', 'token', 'api_token', 'secret',
    ];

    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            app(Auditor::class)->record(
                static::auditAction('created'),
                $model::class,
                (int) $model->getKey(),
                static::auditContext($model->getAttributes()),
            );
        });

        static::updated(function (Model $model): void {
            $changes = $model->getChanges();
            unset($changes['updated_at'], $changes['created_at']);

            if ($changes === []) {
                return;
            }

            app(Auditor::class)->record(
                static::auditAction('updated'),
                $model::class,
                (int) $model->getKey(),
                ['changes' => static::auditContext($changes)],
                null,
                null,
                'warning',
            );
        });

        static::deleted(function (Model $model): void {
            app(Auditor::class)->record(
                static::auditAction('deleted'),
                $model::class,
                (int) $model->getKey(),
                [],
                null,
                null,
                'critical',
            );
        });
    }

    protected static function auditAction(string $event): string
    {
        return Str::snake(class_basename(static::class)).'.'.$event;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected static function auditContext(array $values): array
    {
        $safe = [];

        foreach ($values as $key => $value) {
            $safe[$key] = in_array($key, static::$auditRedactedFields, true)
                ? '[redacted]'
                : $value;
        }

        return $safe;
    }
}
