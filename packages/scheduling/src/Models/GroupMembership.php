<?php

declare(strict_types=1);

namespace Cai\Scheduling\Models;

use Cai\Core\Models\Model;
use Cai\Students\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * scheduling.group_memberships — 03.03 §6. El historial nunca se borra.
 *
 * @property Carbon|null $valid_until
 */
final class GroupMembership extends Model
{
    protected string $schema = 'scheduling';

    protected $table = 'group_memberships';

    /** @var list<string> */
    protected $fillable = ['group_id', 'student_id', 'valid_from', 'valid_until'];

    protected function casts(): array
    {
        return ['valid_from' => 'date', 'valid_until' => 'date'];
    }

    /** @return BelongsTo<Group, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function isActive(): bool
    {
        return $this->valid_until === null || $this->valid_until->isFuture();
    }
}
