<?php

declare(strict_types=1);

namespace Cai\Scheduling\Models;

use Cai\Core\Models\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * scheduling.services — 03.03 §2.
 */
final class Service extends Model
{
    protected string $schema = 'scheduling';

    protected $table = 'services';

    /** @var list<string> */
    protected $fillable = [
        'name', 'description', 'default_duration_minutes', 'modality',
        'color', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return HasMany<Enrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** @return HasMany<Group, $this> */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    /** @return HasMany<Session, $this> */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
