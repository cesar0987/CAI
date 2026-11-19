<?php

declare(strict_types=1);

namespace Cai\Students\Models;

use Cai\Core\Models\Model;
use Database\Factories\FamilyAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * students.family_accounts — cuenta familiar compartida (ADR-0007).
 * El saldo NO se almacena: se calcula en F6 desde cargos e imputaciones.
 *
 * @property int $id
 * @property string $account_code
 * @property string $display_name
 * @property string $status
 */
final class FamilyAccount extends Model
{
    /** @use HasFactory<FamilyAccountFactory> */
    use HasFactory;

    protected string $schema = 'students';

    protected $table = 'family_accounts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'account_code', 'display_name', 'status', 'currency',
        'responsible_guardian_id', 'address', 'phone', 'email', 'notes',
    ];

    /**
     * @return HasMany<FamilyAccountMember, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(FamilyAccountMember::class);
    }

    /**
     * Los hermanos comparten cuenta pero tienen fichas separadas (ADR-0007).
     * La relación es por historial de membresía, no por un FK directo.
     *
     * @return BelongsToMany<Student, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'family_account_members')
            ->withPivot(['role', 'valid_from', 'valid_until'])
            ->whereNull('family_account_members.valid_until');
    }

    protected static function newFactory(): FamilyAccountFactory
    {
        return FamilyAccountFactory::new();
    }
}
