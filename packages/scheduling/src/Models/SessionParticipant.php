<?php

declare(strict_types=1);

namespace Cai\Scheduling\Models;

use Cai\Core\Models\Model;
use Cai\Identity\Models\User;
use Cai\Students\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * scheduling.session_participants — 03.04 §6.
 *
 * @property int $student_id
 */
final class SessionParticipant extends Model
{
    protected string $schema = 'scheduling';

    protected $table = 'session_participants';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'session_id', 'student_id', 'enrollment_id', 'added_by',
    ];

    /** @return BelongsTo<Session, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<Enrollment, $this> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /** @return BelongsTo<User, $this> */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /** @return HasOne<AttendanceRecord, $this> */
    public function attendance(): HasOne
    {
        return $this->hasOne(AttendanceRecord::class, 'participant_id');
    }
}
