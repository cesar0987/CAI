<?php

declare(strict_types=1);

namespace Cai\Scheduling\Models;

use Cai\Core\Models\Model;
use Cai\Identity\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * scheduling.attendance_records — 03.04 §7.
 * Un registro por participante: se corrige con update, nunca se borra.
 */
final class AttendanceRecord extends Model
{
    protected string $schema = 'scheduling';

    protected $table = 'attendance_records';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['participant_id', 'status', 'recorded_by', 'recorded_at', 'notes'];

    protected function casts(): array
    {
        return ['recorded_at' => 'datetime'];
    }

    /** @return BelongsTo<SessionParticipant, $this> */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(SessionParticipant::class, 'participant_id');
    }

    /** @return BelongsTo<User, $this> */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
