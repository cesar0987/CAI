<?php

declare(strict_types=1);

namespace Cai\Care\Models;

use Cai\Care\Authorization\ClinicalScope;
use Cai\Care\Casts\ClinicalEncrypted;
use Cai\Core\Models\Model;
use Cai\Identity\Models\User;
use Cai\Scheduling\Models\Session;
use Cai\Students\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * care.clinical_notes — la tabla más sensible del sistema (03.05 §5, ADR-0006).
 *
 * @property int $student_id
 * @property int $author_user_id
 * @property int|null $session_id
 * @property int|null $care_plan_id
 * @property string $note_type
 * @property string $status
 * @property string $content
 * @property string|null $summary
 * @property int|null $supersedes_note_id
 * @property string|null $correction_reason
 * @property Carbon $recorded_at
 * @property Carbon|null $finalized_at
 */
final class ClinicalNote extends Model
{
    protected string $schema = 'care';

    protected $table = 'clinical_notes';

    /** @var list<string> */
    protected $fillable = [
        'student_id', 'author_user_id', 'session_id', 'care_plan_id',
        'note_type', 'status', 'content', 'summary',
        'supersedes_note_id', 'correction_reason', 'recorded_at', 'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'content' => ClinicalEncrypted::class,
            'recorded_at' => 'immutable_datetime',
            'finalized_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** @return BelongsTo<Session, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    /** @return BelongsTo<CarePlan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(CarePlan::class, 'care_plan_id');
    }

    /** @return BelongsTo<ClinicalNote, $this> */
    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(ClinicalNote::class, 'supersedes_note_id');
    }

    /** @return HasMany<ClinicalNoteAccessGrant, $this> */
    public function grants(): HasMany
    {
        return $this->hasMany(ClinicalNoteAccessGrant::class);
    }

    /**
     * Segunda capa del policy: grant vigente para esta nota (04.03, ADR-0006 §3).
     */
    public function hasActiveGrantFor(int $userId): bool
    {
        return $this->grants()
            ->where('granted_to_user_id', $userId)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->exists();
    }

    /** @param Builder<ClinicalNote> $query */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        return self::applyVisibleTo($query, $user);
    }

    /**
     * Reutilizable desde el Resource de Filament (su query no es `query()`).
     *
     * @param  Builder<ClinicalNote>  $query
     */
    public static function applyVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user === null || ! $user->isActive()) {
            return $query->whereRaw('1 = 0');
        }

        $studentIds = (new ClinicalScope)->scopedStudentIds($user);

        return $query->where(function (Builder $inner) use ($user, $studentIds): void {
            $inner->where('author_user_id', $user->id)
                ->orWhereIn('student_id', $studentIds)
                ->orWhereHas('grants', function (Builder $grants) use ($user): void {
                    $grants->where('granted_to_user_id', $user->id)
                        ->whereNull('revoked_at')
                        ->where('expires_at', '>', now());
                });
        });
    }
}
