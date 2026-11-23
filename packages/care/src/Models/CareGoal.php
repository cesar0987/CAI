<?php

declare(strict_types=1);

namespace Cai\Care\Models;

use Cai\Core\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * care.care_goals — 03.05 §4.
 */
final class CareGoal extends Model
{
    protected string $schema = 'care';

    protected $table = 'care_goals';

    /** @var list<string> */
    protected $fillable = [
        'care_plan_id', 'title', 'description', 'baseline', 'status',
        'target_date', 'review_date', 'progress_notes', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['target_date' => 'date', 'review_date' => 'date'];
    }

    /** @return BelongsTo<CarePlan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(CarePlan::class, 'care_plan_id');
    }
}
