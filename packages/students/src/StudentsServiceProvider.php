<?php

declare(strict_types=1);

namespace Cai\Students;

use Illuminate\Support\ServiceProvider;

final class StudentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        ->loadViewsFrom(__DIR__ . '/../resources/views', 'students');
        ->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'students');
    }
}
