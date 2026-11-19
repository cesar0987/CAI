<?php

declare(strict_types=1);

use Cai\Identity\Actions\AuthenticateUser;
use Cai\Identity\Data\AuthenticateUserData;
use Cai\Identity\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Gate F2: el login no permite enumerar usuarios por tiempo de respuesta.
 * Se mide la ejecución real del caso de uso con bcrypt coste 10 (más estable
 * que el coste 4 de los tests) y se compara la mediana de ambas rutas.
 */
it('takes comparable time for unknown emails and wrong passwords', function () {
    config([
        'hashing.bcrypt.rounds' => 10,
        'cai.seguridad.login_max_attempts' => 100,
    ]);

    $user = User::create([
        'name' => 'Conocido',
        'email' => 'conocido@cai.local',
        'password' => Hash::make('password', ['rounds' => 10]),
        'password_changed_at' => now(),
        'status' => 'active',
    ]);

    $action = app(AuthenticateUser::class);

    // Calentamiento: la primera llamada genera el hash dummy del coste dado.
    $action->execute(new AuthenticateUserData('desconocido@cai.local', 'password'));
    $action->execute(new AuthenticateUserData($user->email, 'incorrecta'));

    $measure = function (string $email) use ($action): float {
        $samples = [];
        foreach (range(1, 7) as $iteration) {
            $started = microtime(true);
            $action->execute(new AuthenticateUserData($email, 'incorrecta'));
            $samples[] = microtime(true) - $started;
        }
        sort($samples);

        return $samples[3]; // mediana de 7 muestras
    };

    $unknown = $measure('desconocido@cai.local');
    $known = $measure($user->email);
    $ratio = max($unknown, $known) / max(min($unknown, $known), 0.000001);

    expect($ratio)->toBeLessThan(
        1.35,
        sprintf('diferencia de tiempos: desconocido %.1f ms, conocido %.1f ms (ratio %.2f)', $unknown * 1000, $known * 1000, $ratio),
    );
});
