<?php

declare(strict_types=1);

namespace Cai\Documents;

use Illuminate\Support\ServiceProvider;

final class DocumentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        ->loadViewsFrom(__DIR__ . '/../resources/views', 'documents');
        ->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'documents');
    }
}
