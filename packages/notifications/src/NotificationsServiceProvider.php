<?php

declare(strict_types=1);

namespace Cai\Notifications;

use Illuminate\Support\ServiceProvider;

final class NotificationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        ->loadViewsFrom(__DIR__ . '/../resources/views', 'notifications');
        ->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'notifications');
    }
}
