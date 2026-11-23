<?php

declare(strict_types=1);

namespace Cai\Scheduling\Models;

use Cai\Core\Models\Model;
use Cai\Students\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * scheduling.enrollments — 03.03 §4. Inscripción ≠ sesión ≠ asistencia.
 */
final class Enrollment extends Model
{
    protected string $schema = 'scheduling';

    protected $table = 'enrollments';

    /** @var list<string> */
    protected $fillable = [
        'student_id', 'service_id', 'start_date', 'end_date', 'status', 'notes',
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

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return HasMany<SessionParticipant, $this> */
    public function participants(): HasMany
    {
        return $this->hasMany(SessionParticipant::class);
    }
}
