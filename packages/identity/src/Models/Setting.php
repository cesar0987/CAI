<?php

declare(strict_types=1);

namespace Cai\Identity\Models;

use Cai\Core\Models\Model;

/**
 * identity.settings — configuración del centro clave/valor tipado (03.01 §5).
 */
final class Setting extends Model
{
    protected string $schema = 'identity';

    protected $table = 'settings';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    public const CREATED_AT = null;

    public const UPDATED_AT = 'updated_at';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group_name',
        'is_public',
        'description',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'array',
            'is_public' => 'boolean',
            'updated_by' => 'integer',
        ];
    }
}
