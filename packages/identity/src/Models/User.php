<?php

declare(strict_types=1);

namespace Cai\Identity\Models;

use Cai\Core\Models\Concerns\ResolvesModuleSchema;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Traits\HasRoles;

/**
 * identity.users — cuenta de acceso al sistema (03.01 §2).
 *
 * Un profesional puede no tener usuario y un usuario puede no ser profesional.
 * Nunca se elimina: se desactiva (`status`) o se usa soft-delete excepcional.
 * `mfa_secret` se guarda cifrado en disco (cast `encrypted`).
 *
 * No es final: `App\Models\User` la extiende como alias de compatibilidad.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $document_id
 * @property int|null $professional_id
 * @property string $status
 * @property Carbon|null $last_login_at
 * @property int $failed_attempts
 * @property Carbon|null $locked_until
 * @property string|null $mfa_secret
 * @property Carbon|null $mfa_enabled_at
 * @property Carbon|null $password_changed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;
    use ResolvesModuleSchema;
    use SoftDeletes;

    protected $table = 'users';

    protected string $schema = 'identity';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'document_id',
        'professional_id',
        'status',
        'email_verified_at',
        'last_login_at',
        'failed_attempts',
        'locked_until',
        'mfa_secret',
        'mfa_enabled_at',
        'password_changed_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mfa_secret' => 'encrypted',
            'mfa_enabled_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'failed_attempts' => 'integer',
            'professional_id' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isLockedOut(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    /**
     * MFA obligatorio para los roles privilegiados configurados (03.01 §9).
     */
    public function requiresMfa(): bool
    {
        if ($this->mfa_enabled_at !== null) {
            return false;
        }

        $enforced = (array) config('cai.seguridad.mfa_enforced_roles', []);

        return $this->roles->pluck('name')->intersect($enforced)->isNotEmpty();
    }

    /**
     * Rotación de contraseña: cambio forzado si pasaron los días configurados
     * o si nunca se cambió (03.01 §9).
     */
    public function requiresPasswordChange(): bool
    {
        $days = (int) config('cai.seguridad.password_rotation_days', 180);

        return $this->password_changed_at === null
            || $this->password_changed_at->lt(now()->subDays($days));
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
