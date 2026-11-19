<?php

declare(strict_types=1);

namespace Cai\Students\Models;

use Cai\Core\Models\Model;
use Cai\Identity\Models\User;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * students.students — ficha del alumno (03.02 §2).
 *
 * @property int $id
 * @property string $student_code
 * @property string $first_name
 * @property string $last_name
 * @property Carbon|null $birth_date
 * @property string $status
 * @property string|null $document_id
 * @property Carbon|null $deleted_at
 */
final class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    use SoftDeletes;

    protected string $schema = 'students';

    protected $table = 'students';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'student_code', 'first_name', 'last_name', 'birth_date', 'gender',
        'status', 'document_id', 'address', 'district', 'city', 'phone',
        'email', 'photo_path', 'admission_date', 'notes', 'created_by', 'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'admission_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * La edad se deriva, nunca se almacena (03.02 §2).
     */
    public function age(): int
    {
        $birthDate = $this->birth_date;

        return $birthDate === null ? 0 : $birthDate->age;
    }

    /**
     * @return BelongsToMany<Guardian, $this>
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardians')
            ->withPivot([
                'relationship', 'is_primary', 'can_pick_up',
                'receives_admin_messages', 'is_emergency_contact', 'valid_from', 'valid_until',
            ]);
    }

    /**
     * @return HasMany<FamilyAccountMember, $this>
     */
    public function familyAccountMemberships(): HasMany
    {
        return $this->hasMany(FamilyAccountMember::class);
    }

    /**
     * @return HasMany<AuthorizedPickup, $this>
     */
    public function authorizedPickups(): HasMany
    {
        return $this->hasMany(AuthorizedPickup::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Alcance de visibilidad para listados y selectores (04.03 §3).
     *
     * F3 no tiene matrículas ni asignaciones (F4): los roles con alcance
     * por asignación se deniegan con fail-close, sin mostrar otros alumnos.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user === null || ! $user->isActive()) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('direccion') || $user->hasRole('secretaria')
            || $user->hasPermissionTo('care.clinical.read')) {
            return $query;
        }

        // El alcance por asignación profesional llega con F4 (scheduling).
        // Hoy: denegación segura en vez de filtrar por una tabla inexistente.
        return $query->whereRaw('1 = 0');
    }

    protected static function newFactory(): StudentFactory
    {
        return StudentFactory::new();
    }
}
