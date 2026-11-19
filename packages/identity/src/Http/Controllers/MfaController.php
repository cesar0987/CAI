<?php

declare(strict_types=1);

namespace Cai\Identity\Http\Controllers;

use Cai\Identity\Audit\Auditor;
use Cai\Identity\Mfa\MfaService;
use Cai\Identity\Mfa\Totp;
use Cai\Identity\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Challenge y configuración de MFA (F2 E-06).
 *
 * Rutas públicas: durante el challenge todavía no hay sesión autenticada.
 * El usuario queda pendiente en sesión y solo se autentica al verificar
 * el código TOTP. Si no hay pendiente, vuelve al login del panel.
 */
final class MfaController extends Controller
{
    public function challenge(): View|RedirectResponse
    {
        $user = $this->pendingUser();

        if ($user === null) {
            return $this->backToLogin();
        }

        return view('identity::mfa.challenge', ['email' => $user->email]);
    }

    public function verify(Request $request, MfaService $service): RedirectResponse
    {
        $user = $this->pendingUser();

        if ($user === null) {
            return $this->backToLogin();
        }

        $code = trim((string) $request->input('code', ''));

        if (! $service->verify($user, $code)) {
            return back()->withErrors(['code' => 'El código no es válido o venció. Intente con el código actual de la app.']);
        }

        return $this->completeLogin($user);
    }

    public function setup(MfaService $service): View|RedirectResponse
    {
        $user = $this->pendingUser();

        if ($user === null || $user->mfa_secret === null) {
            return $this->backToLogin();
        }

        return view('identity::mfa.setup', [
            'email' => $user->email,
            'secret' => $user->mfa_secret,
            'uri' => Totp::provisioningUri($user->mfa_secret, $user->email, (string) config('cai.seguridad.mfa.issuer', 'CAI')),
        ]);
    }

    public function confirmSetup(Request $request, MfaService $service): RedirectResponse
    {
        $user = $this->pendingUser();

        if ($user === null) {
            return $this->backToLogin();
        }

        $code = trim((string) $request->input('code', ''));

        if (! $service->confirmSetup($user->fresh(), $code)) {
            return back()->withErrors(['code' => 'El código no coincide. Vuelva a escanear o pruebe con el código actual.']);
        }

        return $this->completeLogin($user->fresh());
    }

    private function completeLogin(User $user): RedirectResponse
    {
        $remember = (bool) session('mfa.remember', false);

        Auth::login($user, $remember);
        session()->regenerate();
        session()->forget(['mfa.pending_user_id', 'mfa.remember']);

        app(Auditor::class)->record('auth.login', User::class, $user->id, ['ip' => request()->ip()], $user->id);

        return redirect()->intended(Filament::getUrl());
    }

    private function pendingUser(): ?User
    {
        $id = session('mfa.pending_user_id');

        return is_int($id) ? User::find($id) : null;
    }

    private function backToLogin(): RedirectResponse
    {
        return redirect()->route('filament.panel.auth.login');
    }
}
