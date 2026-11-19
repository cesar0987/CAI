<?php

declare(strict_types=1);

namespace Cai\Identity\Models;

use Cai\Core\Models\Model;
use Cai\Identity\Exceptions\AuditAppendOnlyException;

/**
 * identity.audit_events — append-only (03.01 §7, 04.05).
 *
 * Sin updated_at ni deleted_at. El modelo rechaza updates y deletes como
 * defensa en profundidad: la UI y las policies tampoco los exponen.
 */
final class AuditEvent extends Model
{
    protected string $schema = 'identity';

    protected $table = 'audit_events';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'actor_user_id',
        'actor_role',
        'action',
        'entity_type',
        'entity_id',
        'student_id',
        'changes',
        'context',
        'severity',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'context' => 'array',
            'created_at' => 'datetime',
            'actor_user_id' => 'integer',
            'student_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        self::updating(function (): void {
            throw new AuditAppendOnlyException('audit_events es append-only: no se modifica.');
        });

        self::deleting(function (): void {
            throw new AuditAppendOnlyException('audit_events es append-only: no se borra.');
        });
    }
}
