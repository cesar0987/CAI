<?php

declare(strict_types=1);

namespace Cai\Identity\Actions;

use Cai\Core\Support\Result;
use Cai\Identity\Audit\Auditor;
use Cai\Identity\Data\AuthenticateUserData;
use Cai\Identity\Exceptions\AccountInactiveException;
use Cai\Identity\Exceptions\AccountLockedException;
use Cai\Identity\Exceptions\InvalidCredentialsException;
use Cai\Identity\Exceptions\PasswordChangeRequiredException;
use Cai\Identity\Models\SecurityEvent;
use Cai\Identity\Models\User;
use Cai\Identity\Results\AuthenticateUserResult;
use Illuminate\Support\Facades\Hash;

/**
 * Autenticación por contraseña — 08.04 §2.2.
 *
 * Reglas: mensaje único anti-enumeración (con hash dummy si el usuario no
 * existe), bloqueo a los 5 intentos / 15 min, cuenta inactiva rechazada,
 * rotación de contraseña forzada y MFA obligatorio para los roles
 * privilegiados. El resultado indica si falta el código TOTP o la
 * configuración de MFA; la sesión se abre recién con estado authenticated.
 */
final class AuthenticateUser
{
    /**
     * Hash bcrypt fijo para igualar el tiempo de respuesta cuando el
     * usuario no existe (anti-enumeración por tiempo). Se genera con el
     * coste configurado para que sea comparable con el hash real.
     *
     * @var array<int, string>
     */
    private static array $dummyHashes = [];

    public function __construct(
        private readonly Auditor $auditor,
    ) {}

    public function execute(AuthenticateUserData $data): Result
    {
        $email = strtolower(trim($data->email));

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return Result::failure(new InvalidCredentialsException);
        }

        $user = User::where('email', $email)->first();

        if ($user === null) {
            Hash::check($data->password, $this->dummyHash());

            return Result::failure(new InvalidCredentialsException);
        }

        if (! Hash::check($data->password, $user->password)) {
            $this->registerFailedAttempt($user, $email);

            return Result::failure(new InvalidCredentialsException);
        }

        if ($user->isLockedOut()) {
            return Result::failure(new AccountLockedException);
        }

        if (! $user->isActive()) {
            return Result::failure(new AccountInactiveException);
        }

        if ($user->requiresPasswordChange()) {
            return Result::failure(new PasswordChangeRequiredException);
        }

        $user->forceFill([
            'failed_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'password_changed_at' => $user->password_changed_at ?? now(),
        ])->save();

        if ($user->mfa_enabled_at !== null) {
            return Result::success(new AuthenticateUserResult($user, AuthenticateUserResult::STATE_MFA_REQUIRED));
        }

        if ($user->requiresMfa()) {
            return Result::success(new AuthenticateUserResult($user, AuthenticateUserResult::STATE_MFA_SETUP_REQUIRED));
        }

        $this->auditor->record('auth.login', User::class, $user->id, ['ip' => request()->ip()], $user->id);

        return Result::success(new AuthenticateUserResult($user));
    }

    private function dummyHash(): string
    {
        $rounds = (int) config('hashing.bcrypt.rounds', 12);

        return self::$dummyHashes[$rounds] ??= Hash::make(
            'cai-dummy-anti-enumeracion',
            ['rounds' => $rounds],
        );
    }

    private function registerFailedAttempt(User $user, string $email): void
    {
        $attempts = $user->failed_attempts + 1;
        $maxAttempts = (int) config('cai.seguridad.login_max_attempts', 5);
        $lockoutMinutes = (int) config('cai.seguridad.login_lockout_minutes', 15);
        $locked = $attempts >= $maxAttempts;

        $user->forceFill([
            'failed_attempts' => $attempts,
            'locked_until' => $locked ? now()->addMinutes($lockoutMinutes) : $user->locked_until,
        ])->save();

        $this->auditor->record(
            'auth.login_failed',
            User::class,
            $user->id,
            ['email' => $email, 'attempts' => $attempts, 'ip' => request()->ip()],
            $user->id,
            null,
            'warning',
        );

        if ($locked) {
            SecurityEvent::create([
                'event_type' => 'auth.failed_repeated',
                'severity' => 'critical',
                'user_id' => $user->id,
                'reason' => "Cuenta bloqueada {$lockoutMinutes} min tras {$attempts} intentos fallidos",
                'metadata' => ['email' => $email, 'attempts' => $attempts],
                'ip_address' => request()->ip(),
            ]);
        }
    }
}
