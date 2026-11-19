<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Pages\Auth;

use Cai\Identity\Actions\AuthenticateUser;
use Cai\Identity\Data\AuthenticateUserData;
use Cai\Identity\Mfa\MfaService;
use Cai\Identity\Results\AuthenticateUserResult;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;

/**
 * Login del panel con challenge MFA (F2 E-02/E-06).
 *
 * La lógica de negocio sigue en AuthenticateUser / MfaService: esta página
 * solo decide a dónde ir según el estado devuelto. Nunca abre la sesión
 * hasta que el código TOTP esté verificado.
 */
final class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $result = app(AuthenticateUser::class)->execute(
            new AuthenticateUserData($data['email'], $data['password']),
        );

        if ($result->isFailure()) {
            // Mensaje idéntico para credenciales inválidas, cuenta inactiva
            // o rotación vencida: no se filtra el estado de la cuenta.
            $this->throwFailureValidationException();
        }

        /** @var AuthenticateUserResult $authentication */
        $authentication = $result->unwrap();
        $user = $authentication->user;

        session([
            'mfa.pending_user_id' => $user->id,
            'mfa.remember' => (bool) ($data['remember'] ?? false),
        ]);

        if ($authentication->state === AuthenticateUserResult::STATE_MFA_REQUIRED) {
            $this->redirectRoute('mfa.challenge', navigate: false);

            return null;
        }

        if ($authentication->state === AuthenticateUserResult::STATE_MFA_SETUP_REQUIRED) {
            app(MfaService::class)->startSetup($user);
            $this->redirectRoute('mfa.setup', navigate: false);

            return null;
        }

        Filament::auth()->login($user, (bool) ($data['remember'] ?? false));
        session()->regenerate();

        return app(LoginResponse::class);
    }
}
