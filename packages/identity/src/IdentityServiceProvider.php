<?php

declare(strict_types=1);

namespace Cai\Identity;

use Illuminate\Support\ServiceProvider;

final class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        ->loadViewsFrom(__DIR__ . '/../resources/views', 'identity');
        ->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'identity');
    }
}
