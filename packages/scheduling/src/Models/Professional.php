<?php

declare(strict_types=1);

namespace Cai\Scheduling\Models;

use Cai\Core\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * scheduling.professionals — 03.03 §3.
 *
 * @property string $status
 */
final class Professional extends Model
{
    protected string $schema = 'scheduling';

    protected $table = 'professionals';

    /** @var list<string> */
    protected $fillable = [
        'user_id', 'display_name', 'specialty', 'document_id', 'phone',
        'email', 'license_number', 'status', 'hired_at', 'notes',
    ];

    /** @return HasMany<ProfessionalAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(ProfessionalAssignment::class);
    }

    /** @return HasMany<AvailabilityRule, $this> */
    public function availabilityRules(): HasMany
    {
        return $this->hasMany(AvailabilityRule::class);
    }

    /** @return BelongsToMany<Session, $this> */
    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(Session::class, 'session_professionals')
            ->withPivot(['assignment_role', 'starts_at', 'ends_at']);
    }

    public function isAvailableForNewAssignments(): bool
    {
        return $this->status === 'active';
    }
}
