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
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
