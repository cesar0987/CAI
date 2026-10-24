<?php

declare(strict_types=1);

namespace Cai\Core\Providers;

use Cai\Core\Console\Commands\DbReset;
use Cai\Core\Console\Commands\MigrateModules;
use Illuminate\Support\ServiceProvider;

final class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(base_path('config/cai.php'), 'cai');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrateModules::class,
                DbReset::class,
            ]);
        }
    }
}
