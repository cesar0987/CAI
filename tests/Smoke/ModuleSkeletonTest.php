<?php

declare(strict_types=1);
use Cai\Billing\BillingServiceProvider;
use Cai\Care\CareServiceProvider;
use Cai\Core\Providers\CoreServiceProvider;
use Cai\Documents\DocumentsServiceProvider;
use Cai\Identity\IdentityServiceProvider;
use Cai\Notifications\NotificationsServiceProvider;
use Cai\Reporting\ReportingServiceProvider;
use Cai\Scheduling\SchedulingServiceProvider;
use Cai\Students\StudentsServiceProvider;
use Illuminate\Contracts\Console\Kernel;

/**
 * Smoke test del esqueleto.
 *
 * Corre solo en `main` (ver .github/workflows/ci.yml) y verifica que la
 * aplicación arranca con los módulos y los comandos esperados. No toca la base:
 * eso lo hace el paso previo `cai:db:reset`.
 */
it('registra los nueve proveedores de módulo', function () {
    $providers = [
        BillingServiceProvider::class,
        CareServiceProvider::class,
        CoreServiceProvider::class,
        DocumentsServiceProvider::class,
        IdentityServiceProvider::class,
        NotificationsServiceProvider::class,
        ReportingServiceProvider::class,
        SchedulingServiceProvider::class,
        StudentsServiceProvider::class,
    ];

    foreach ($providers as $provider) {
        expect(app()->getProvider($provider))->not->toBeNull();
    }
});

it('declara los ocho schemas de los módulos', function () {
    expect(config('cai.schemas'))->toBe([
        'identity',
        'students',
        'scheduling',
        'care',
        'billing',
        'documents',
        'notifications',
        'reporting',
    ]);
});

it('registra los comandos de migración por módulo', function () {
    $commands = array_keys(app(Kernel::class)->all());

    expect($commands)
        ->toContain('cai:migrate')
        ->toContain('cai:db:reset');
});
