<?php

declare(strict_types=1);

namespace Cai\Identity\Models;

use Cai\Core\Models\Model;

/**
 * identity.security_events — eventos de seguridad con severidad (03.01 §8).
 */
final class SecurityEvent extends Model
{
    protected string $schema = 'identity';

    protected $table = 'security_events';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_type',
        'severity',
        'user_id',
        'student_id',
        'resource_type',
        'resource_id',
        'reason',
        'metadata',
        'ip_address',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
            'user_id' => 'integer',
            'student_id' => 'integer',
        ];
    }
}
