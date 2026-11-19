<?php

declare(strict_types=1);

use Cai\Identity\Actions\AuthenticateUser;
use Cai\Identity\Data\AuthenticateUserData;
use Cai\Identity\Mfa\MfaService;
use Cai\Identity\Mfa\Totp;
use Cai\Identity\Models\Role;
use Cai\Identity\Models\User;
use Cai\Identity\Results\AuthenticateUserResult;
use Illuminate\Support\Facades\DB;

it('starts MFA setup without enabling it until a valid code is confirmed', function () {
    $user = User::factory()->create(['password_changed_at' => now()]);
    $service = app(MfaService::class);

    $setup = $service->startSetup($user);

    expect($setup['secret'])->toHaveLength(32)
        ->and($setup['uri'])->toStartWith('otpauth://totp/CAI:')
        ->and($user->fresh()->mfa_enabled_at)->toBeNull()
        ->and($user->fresh()->mfa_secret)->toBe($setup['secret']);

    // El secreto queda cifrado en disco
    $raw = DB::table('users')->where('id', $user->id)->value('mfa_secret');
    expect($raw)->not->toBe($setup['secret']);

    // Código incorrecto: no se activa
    expect($service->confirmSetup($user->fresh(), '000000'))->toBeFalse()
        ->and($user->fresh()->mfa_enabled_at)->toBeNull();

    // Código correcto de la app: se activa
    $code = Totp::code($setup['secret']);
    expect($service->confirmSetup($user->fresh(), $code))->toBeTrue()
        ->and($user->fresh()->mfa_enabled_at)->not->toBeNull();

    expect(DB::table('audit_events')->whereIn('action', ['mfa.setup_started', 'mfa.setup_failed', 'mfa.enabled'])->count())->toBe(3);
});

it('verifies codes only for enabled accounts and audits failures', function () {
    $service = app(MfaService::class);
    $user = User::factory()->create(['password_changed_at' => now()]);

    // Sin MFA activo no hay challenge que valga
    expect($service->verify($user, '123456'))->toBeFalse();

    $setup = $service->startSetup($user);
    $service->confirmSetup($user->fresh(), Totp::code($setup['secret']));
    $user = $user->fresh();

    expect($service->verify($user, Totp::code($setup['secret'])))->toBeTrue()
        ->and($service->verify($user, '000000'))->toBeFalse();

    expect(DB::table('audit_events')->where('action', 'auth.mfa_failed')->count())->toBe(1);
});

it('records mfa.disabled as critical for privileged accounts', function () {
    Role::create(['name' => 'direccion', 'label' => 'Dirección']);
    $service = app(MfaService::class);

    $privileged = User::factory()->create(['password_changed_at' => now()]);
    $privileged->assignRole('direccion');
    $setup = $service->startSetup($privileged);
    $service->confirmSetup($privileged->fresh(), Totp::code($setup['secret']));

    $service->disable($privileged->fresh());

    $event = DB::table('security_events')->where('event_type', 'mfa.disabled')->first();
    expect($event->severity)->toBe('critical')
        ->and($event->user_id)->toBe($privileged->id)
        ->and($privileged->fresh()->mfa_secret)->toBeNull()
        ->and($privileged->fresh()->mfa_enabled_at)->toBeNull();

    // Cuenta no privilegiada: warning
    $regular = User::factory()->create(['password_changed_at' => now()]);
    $setup = $service->startSetup($regular);
    $service->confirmSetup($regular->fresh(), Totp::code($setup['secret']));
    $service->disable($regular->fresh());

    $events = DB::table('security_events')->where('event_type', 'mfa.disabled')->orderBy('id')->get();
    expect($events)->toHaveCount(2)
        ->and($events[1]->severity)->toBe('warning');
});

it('completes the full privileged login flow: password, setup, code', function () {
    Role::create(['name' => 'direccion', 'label' => 'Dirección']);
    $user = User::factory()->create(['email' => 'direccion@cai.local', 'password_changed_at' => now()]);
    $user->assignRole('direccion');

    $service = app(MfaService::class);
    $action = app(AuthenticateUser::class);

    // 1. Contraseña correcta, pero el rol exige configurar MFA
    $result = $action->execute(new AuthenticateUserData('direccion@cai.local', 'password'));
    expect($result->unwrap()->state)->toBe(AuthenticateUserResult::STATE_MFA_SETUP_REQUIRED);

    // 2. Configuración con la app autenticadora
    $setup = $service->startSetup($user->fresh());
    expect($service->confirmSetup($user->fresh(), Totp::code($setup['secret'])))->toBeTrue();

    // 3. Nuevo login: ahora pide el código
    $result = $action->execute(new AuthenticateUserData('direccion@cai.local', 'password'));
    expect($result->unwrap()->state)->toBe(AuthenticateUserResult::STATE_MFA_REQUIRED);

    // 4. Código vigente: autenticación completa
    expect($service->verify($result->unwrap()->user, Totp::code($setup['secret'])))->toBeTrue();
});
