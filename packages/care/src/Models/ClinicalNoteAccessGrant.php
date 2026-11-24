<?php

declare(strict_types=1);

namespace Cai\Care\Models;

use Cai\Core\Models\Model;
use Cai\Identity\Models\User;
use Cai\Students\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * care.clinical_note_access_grants — 03.05 §6.
 *
 * @property int|null $clinical_note_id
 * @property int $student_id
 * @property int $granted_to_user_id
 * @property int $granted_by_user_id
 * @property string $reason
 * @property Carbon $expires_at
 * @property Carbon|null $revoked_at
 * @property int|null $revoked_by_user_id
 * @property string|null $revocation_reason
 */
final class ClinicalNoteAccessGrant extends Model
{
    protected string $schema = 'care';

    protected $table = 'clinical_note_access_grants';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'clinical_note_id', 'student_id', 'granted_to_user_id', 'granted_by_user_id',
        'reason', 'expires_at', 'revoked_at', 'revoked_by_user_id', 'revocation_reason',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'immutable_datetime', 'revoked_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<ClinicalNote, $this> */
    public function note(): BelongsTo
    {
        return $this->belongsTo(ClinicalNote::class, 'clinical_note_id');
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<User, $this> */
    public function grantedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_to_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null && $this->expires_at->isFuture();
    }
}
