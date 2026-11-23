<?php

declare(strict_types=1);

namespace Cai\Care\Models;

use Cai\Core\Models\Model;
use Cai\Scheduling\Models\Professional;
use Cai\Students\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * care.care_plans — 03.05 §2.
 *
 * @property int $student_id
 */
final class CarePlan extends Model
{
    protected string $schema = 'care';

    protected $table = 'care_plans';

    /** @var list<string> */
    protected $fillable = [
        'student_id', 'title', 'diagnosis', 'start_date', 'end_date', 'status',
        'responsible_professional_id', 'goals_summary', 'review_interval_days', 'created_by',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<Professional, $this> */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Professional::class, 'responsible_professional_id');
    }

    /** @return BelongsToMany<Professional, $this> */
    public function professionals(): BelongsToMany
    {
        return $this->belongsToMany(Professional::class, 'care_plan_professionals')
            ->withPivot(['role']);
    }

    /** @return HasMany<CareGoal, $this> */
    public function goals(): HasMany
    {
        return $this->hasMany(CareGoal::class);
    }

    /** @return HasMany<ClinicalNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(ClinicalNote::class);
    }
}
