<?php

declare(strict_types=1);

namespace Cai\Identity\Models;

use Cai\Core\Models\Model;

/**
 * identity.feature_flags — kill switches (03.01 §6, 06.04).
 */
final class FeatureFlag extends Model
{
    protected string $schema = 'identity';

    protected $table = 'feature_flags';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'description',
        'is_enabled',
        'scope',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'updated_by' => 'integer',
        ];
    }
}
