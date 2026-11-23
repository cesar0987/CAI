<?php

declare(strict_types=1);

namespace Cai\Scheduling\Models;

use Cai\Core\Models\Model;
use Cai\Students\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * scheduling.professional_assignments — 03.03 §7.
 * Base del alcance clínico que consultará `care` en F5.
 *
 * @property Carbon|null $valid_until
 */
final class ProfessionalAssignment extends Model
{
    protected string $schema = 'scheduling';

    protected $table = 'professional_assignments';

    /** @var list<string> */
    protected $fillable = [
        'professional_id', 'student_id', 'role', 'valid_from', 'valid_until',
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
