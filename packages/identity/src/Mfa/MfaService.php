<?php

declare(strict_types=1);

namespace Cai\Identity\Mfa;

use Cai\Identity\Audit\Auditor;
use Cai\Identity\Models\SecurityEvent;
use Cai\Identity\Models\User;

/**
 * Ciclo de vida del MFA (04.01 §6, 03.01 §9): TOTP obligatorio para
 * direccion y admin_tecnico, opcional para el resto.
 *
 * El secreto se guarda cifrado en disco (cast `encrypted` del modelo) y
 * solo se activa cuando el usuario demuestra que su app genera el código
 * correcto (confirmación con un código vigente).
 */
final class MfaService
{
    public function __construct(
        private readonly Auditor $auditor,
    ) {}

    /**
     * Inicia la configuración: genera el secreto y devuelve la URI para
     * la app autenticadora. MFA todavía NO queda activo.
     *
     * @return array{secret: string, uri: string}
     */
    public function startSetup(User $user): array
    {
        $secret = Totp::generateSecret();

        $user->forceFill([
            'mfa_secret' => $secret,
            'mfa_enabled_at' => null,
        ])->save();

        $this->auditor->record('mfa.setup_started', User::class, $user->id, [], $user->id);

        return [
            'secret' => $secret,
            'uri' => Totp::provisioningUri(
                $secret,
                $user->email,
                (string) config('cai.seguridad.mfa.issuer', 'CAI'),
            ),
        ];
    }

    /**
     * Confirma la configuración con un código de la app. Sin código válido
     * no se activa: la cuenta privilegiada no puede quedar a medias.
     */
    public function confirmSetup(User $user, string $code): bool
    {
        if ($user->mfa_secret === null || ! Totp::verify($user->mfa_secret, $code)) {
            $this->auditor->record('mfa.setup_failed', User::class, $user->id, [], $user->id, null, 'warning');

            return false;
        }

        $user->forceFill(['mfa_enabled_at' => now()])->save();

        $this->auditor->record('mfa.enabled', User::class, $user->id, [], $user->id);

        return true;
    }

    /**
     * Verifica el código en el challenge de login.
     */
    public function verify(User $user, string $code): bool
    {
        if ($user->mfa_enabled_at === null || $user->mfa_secret === null) {
            return false;
        }

        $valid = Totp::verify($user->mfa_secret, $code);

        if (! $valid) {
            $this->auditor->record('auth.mfa_failed', User::class, $user->id, [], $user->id, null, 'warning');
        }

        return $valid;
    }

    /**
     * Desactiva MFA. En una cuenta privilegiada es evento de seguridad
     * `critical` (03.01 §8: mfa.disabled); en el resto, warning.
     */
    public function disable(User $user): void
    {
        $enforced = (array) config('cai.seguridad.mfa_enforced_roles', []);
        $privileged = $user->roles->pluck('name')->intersect($enforced)->isNotEmpty();

        $user->forceFill([
            'mfa_secret' => null,
            'mfa_enabled_at' => null,
        ])->save();

        $this->auditor->record('mfa.disabled', User::class, $user->id, [], $user->id, null, 'critical');

        SecurityEvent::create([
            'event_type' => 'mfa.disabled',
            'severity' => $privileged ? 'critical' : 'warning',
            'user_id' => $user->id,
            'reason' => $privileged
                ? 'MFA desactivado en una cuenta con rol privilegiado'
                : 'MFA desactivado por el usuario o un administrador',
            'ip_address' => request()->ip(),
        ]);
    }
}
