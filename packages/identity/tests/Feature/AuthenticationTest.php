<?php

declare(strict_types=1);

use Cai\Identity\Actions\AuthenticateUser;
use Cai\Identity\Data\AuthenticateUserData;
use Cai\Identity\Exceptions\AccountInactiveException;
use Cai\Identity\Exceptions\AccountLockedException;
use Cai\Identity\Exceptions\InvalidCredentialsException;
use Cai\Identity\Exceptions\PasswordChangeRequiredException;
use Cai\Identity\Models\Role;
use Cai\Identity\Models\User;
use Cai\Identity\Results\AuthenticateUserResult;
use Illuminate\Support\Facades\DB;

function authenticate(string $email, string $password): mixed
{
    return app(AuthenticateUser::class)->execute(new AuthenticateUserData($email, $password));
}

function activeUser(array $attributes = []): User
{
    return User::factory()->create(array_merge(['password_changed_at' => now()], $attributes));
}

it('authenticates valid credentials and records the login', function () {
    $user = activeUser(['email' => 'direccion@cai.local', 'failed_attempts' => 2]);

    $result = authenticate('direccion@cai.local', 'password');

    expect($result->isSuccess())->toBeTrue()
        ->and($result->unwrap()->state)->toBe(AuthenticateUserResult::STATE_AUTHENTICATED)
        ->and($result->unwrap()->user->id)->toBe($user->id);

    $fresh = $user->fresh();
    expect($fresh->failed_attempts)->toBe(0)
        ->and($fresh->last_login_at)->not->toBeNull();

    expect(DB::table('audit_events')->where('action', 'auth.login')->count())->toBe(1);
});

it('returns the same error for unknown email and wrong password (anti-enumeration)', function () {
    activeUser(['email' => 'existe@cai.local']);

    $unknown = authenticate('no-existe@cai.local', 'password');
    $wrong = authenticate('existe@cai.local', 'incorrecta');

    expect($unknown->isFailure())->toBeTrue()
        ->and($wrong->isFailure())->toBeTrue()
        ->and($unknown->error())->toBeInstanceOf(InvalidCredentialsException::class)
        ->and($wrong->error())->toBeInstanceOf(InvalidCredentialsException::class)
        ->and($unknown->error()->getMessage())->toBe($wrong->error()->getMessage());
});

it('counts failed attempts and locks the account after the configured maximum', function () {
    $user = activeUser(['email' => 'atacado@cai.local']);

    foreach (range(1, 5) as $attempt) {
        $result = authenticate('atacado@cai.local', 'incorrecta');
        expect($result->error())->toBeInstanceOf(InvalidCredentialsException::class);
        expect($user->fresh()->failed_attempts)->toBe($attempt);
    }

    $fresh = $user->fresh();
    expect($fresh->locked_until)->not->toBeNull()
        ->and($fresh->isLockedOut())->toBeTrue();

    // Bloqueo registrado como evento de seguridad critical (03.01 §8)
    $event = DB::table('security_events')->where('event_type', 'auth.failed_repeated')->first();
    expect($event)->not->toBeNull()
        ->and($event->severity)->toBe('critical')
        ->and($event->user_id)->toBe($user->id);

    // Con la contraseña correcta, la cuenta bloqueada tampoco entra
    $result = authenticate('atacado@cai.local', 'password');
    expect($result->error())->toBeInstanceOf(AccountLockedException::class);
});

it('allows login again once the lockout expires', function () {
    $user = activeUser([
        'email' => 'bloqueado@cai.local',
        'failed_attempts' => 5,
        'locked_until' => now()->subMinute(),
    ]);

    $result = authenticate('bloqueado@cai.local', 'password');

    expect($result->isSuccess())->toBeTrue()
        ->and($user->fresh()->failed_attempts)->toBe(0)
        ->and($user->fresh()->locked_until)->toBeNull();
});

it('rejects inactive accounts', function () {
    activeUser(['email' => 'baja@cai.local', 'status' => 'inactive']);

    $result = authenticate('baja@cai.local', 'password');

    expect($result->error())->toBeInstanceOf(AccountInactiveException::class);
});

it('forces password change when rotation is due', function () {
    activeUser(['email' => 'rotacion@cai.local', 'password_changed_at' => now()->subDays(181)]);

    $result = authenticate('rotacion@cai.local', 'password');

    expect($result->error())->toBeInstanceOf(PasswordChangeRequiredException::class);
});

it('keeps role assignments visible across queries (morph class regression)', function () {
    Role::create(['name' => 'direccion', 'label' => 'Dirección']);
    $user = User::factory()->create(['email' => 'morph@cai.local', 'password_changed_at' => now()]);

    // La factory debe instanciar el modelo del módulo, no el alias deprecado:
    // el model_type del pivot polimórfico tiene que coincidir en toda consulta.
    expect($user)->toBeInstanceOf(User::class)
        ->and($user->getMorphClass())->toBe(User::class);

    $user->assignRole('direccion');

    $fetched = User::where('email', 'morph@cai.local')->first();
    expect($fetched->hasRole('direccion'))->toBeTrue()
        ->and($fetched->requiresMfa())->toBeTrue();
});

it('requires MFA setup for privileged roles without MFA', function () {
    Role::create(['name' => 'direccion', 'label' => 'Dirección']);
    Role::create(['name' => 'admin_tecnico', 'label' => 'Administración Técnica']);

    foreach (['direccion', 'admin_tecnico'] as $roleName) {
        $user = activeUser(['email' => $roleName.'@cai.local']);
        $user->assignRole($roleName);

        $result = authenticate($roleName.'@cai.local', 'password');

        expect($result->isSuccess())->toBeTrue()
            ->and($result->unwrap()->state)->toBe(AuthenticateUserResult::STATE_MFA_SETUP_REQUIRED);
    }
});

it('requires the TOTP code when MFA is already enabled', function () {
    $user = activeUser([
        'email' => 'conmfa@cai.local',
        'mfa_secret' => 'JBSWY3DPEHPK3PXP',
        'mfa_enabled_at' => now(),
    ]);

    $result = authenticate('conmfa@cai.local', 'password');

    expect($result->unwrap()->state)->toBe(AuthenticateUserResult::STATE_MFA_REQUIRED)
        ->and($result->unwrap()->isFullyAuthenticated())->toBeFalse();
});

it('does not require MFA for non-privileged roles', function () {
    Role::create(['name' => 'secretaria', 'label' => 'Secretaría']);
    $user = activeUser(['email' => 'secretaria@cai.local']);
    $user->assignRole('secretaria');

    $result = authenticate('secretaria@cai.local', 'password');

    expect($result->unwrap()->state)->toBe(AuthenticateUserResult::STATE_AUTHENTICATED);
});
