<?php

declare(strict_types=1);

namespace Cai\Care;

use Illuminate\Support\ServiceProvider;

final class CareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        ->loadViewsFrom(__DIR__ . '/../resources/views', 'care');
        ->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'care');
    }
}
