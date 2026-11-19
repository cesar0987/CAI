<?php

declare(strict_types=1);

namespace Cai\Students\Models;

use Cai\Core\Models\Model;
use Database\Factories\FamilyAccountMemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * students.family_account_members — historial de pertenencia (03.02 §6).
 * Las filas se cierran con valid_until; nunca se borran (I-04).
 *
 * @property int $id
 * @property int $family_account_id
 * @property int $student_id
 * @property string $role
 * @property Carbon $valid_from
 * @property Carbon|null $valid_until
 */
final class FamilyAccountMember extends Model
{
    /** @use HasFactory<FamilyAccountMemberFactory> */
    use HasFactory;

    protected string $schema = 'students';

    protected $table = 'family_account_members';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'family_account_id', 'student_id', 'role', 'valid_from', 'valid_until',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['valid_from' => 'date', 'valid_until' => 'date'];
    }

    /**
     * @return BelongsTo<FamilyAccount, $this>
     */
    public function familyAccount(): BelongsTo
    {
        return $this->belongsTo(FamilyAccount::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function isActive(): bool
    {
        return $this->valid_until === null || $this->valid_until->isFuture();
    }

    protected static function newFactory(): FamilyAccountMemberFactory
    {
        return FamilyAccountMemberFactory::new();
    }
}
