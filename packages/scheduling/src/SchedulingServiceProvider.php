<?php

declare(strict_types=1);

namespace Cai\Scheduling;

use Illuminate\Support\ServiceProvider;

final class SchedulingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        ->loadViewsFrom(__DIR__ . '/../resources/views', 'scheduling');
        ->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'scheduling');
    }
}
