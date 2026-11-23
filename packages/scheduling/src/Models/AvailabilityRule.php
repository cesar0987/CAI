<?php

declare(strict_types=1);

namespace Cai\Scheduling\Models;

use Cai\Core\Models\Model;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * scheduling.availability_rules — 03.03 §8.
 * `weekday`: 0 = domingo … 6 = sábado, igual que en PostgreSQL.
 *
 * @property int $weekday
 * @property Carbon $valid_from
 * @property Carbon|null $valid_until
 */
final class AvailabilityRule extends Model
{
    protected string $schema = 'scheduling';

    protected $table = 'availability_rules';

    /** @var list<string> */
    protected $fillable = [
        'professional_id', 'weekday', 'start_time', 'end_time',
        'room', 'valid_from', 'valid_until',
    ];

    protected function casts(): array
    {
        return ['valid_from' => 'date', 'valid_until' => 'date'];
    }

    /** @return BelongsTo<Professional, $this> */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function occursOn(CarbonInterface $day): bool
    {
        if ((int) $day->dayOfWeek !== (int) $this->weekday) {
            return false;
        }

        if ($day->toDateString() < $this->valid_from->toDateString()) {
            return false;
        }

        return $this->valid_until === null || $day->toDateString() <= $this->valid_until->toDateString();
    }

    public function isVigente(): bool
    {
        return $this->valid_until === null || $this->valid_until->isFuture();
    }
}
