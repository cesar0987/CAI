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
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
