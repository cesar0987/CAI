<?php

declare(strict_types=1);

namespace Cai\Identity\Results;

/**
 * Resultado de la autenticación por contraseña (08.04 §2.2).
 *
 * El estado indica el paso siguiente del flujo:
 * - `authenticated`: credenciales completas, se puede abrir sesión.
 * - `mfa_required`: la cuenta tiene MFA activo; falta el código TOTP.
 * - `mfa_setup_required`: rol privilegiado sin MFA; debe configurarlo
 *   antes de entrar (03.01 §11: primer ingreso).
 *
 * La sesión NO se abre hasta que el estado sea `authenticated`.
 */
final readonly class AuthenticateUserResult
{
    public const STATE_AUTHENTICATED = 'authenticated';

    public const STATE_MFA_REQUIRED = 'mfa_required';

    public const STATE_MFA_SETUP_REQUIRED = 'mfa_setup_required';

    public function __construct(
        public mixed $user,
        public string $state = self::STATE_AUTHENTICATED,
    ) {}

    public function isFullyAuthenticated(): bool
    {
        return $this->state === self::STATE_AUTHENTICATED;
    }
}
