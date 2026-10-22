<?php

declare(strict_types=1);

namespace Cai\Billing;

use Illuminate\Support\ServiceProvider;

final class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        ->loadViewsFrom(__DIR__ . '/../resources/views', 'billing');
        ->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'billing');
    }
}
