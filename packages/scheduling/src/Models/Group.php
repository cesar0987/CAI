<?php

declare(strict_types=1);

namespace Cai\Scheduling\Models;

use Cai\Core\Models\Model;
use Cai\Students\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * scheduling.groups — 03.03 §5.
 *
 * @property string $name
 * @property int $capacity
 */
final class Group extends Model
{
    protected string $schema = 'scheduling';

    protected $table = 'groups';

    /** @var list<string> */
    protected $fillable = [
        'service_id', 'name', 'responsible_professional_id', 'room',
        'capacity', 'max_duration_minutes', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return BelongsTo<Professional, $this> */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Professional::class, 'responsible_professional_id');
    }

    /** @return HasMany<GroupMembership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class);
    }

    /** @return HasMany<Session, $this> */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /** @return BelongsToMany<Student, $this> */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'group_memberships')
            ->withPivot(['valid_from', 'valid_until'])
            ->whereNull('group_memberships.valid_until');
    }
}
