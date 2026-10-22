<?php

declare(strict_types=1);

namespace Cai\Reporting;

use Illuminate\Support\ServiceProvider;

final class ReportingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        ->loadViewsFrom(__DIR__ . '/../resources/views', 'reporting');
        ->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'reporting');
    }
}
