<?php

declare(strict_types=1);

namespace Cai\Students;

use Cai\Students\Contracts\StudentDirectory;
use Cai\Students\Services\StudentDirectoryService;
use Cai\Students\Support\StudentCodeGenerator;
use Illuminate\Support\ServiceProvider;

final class StudentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StudentCodeGenerator::class);
        $this->app->singleton(StudentDirectory::class, StudentDirectoryService::class);

        if ($this->app->runningInConsole()) {
            $this->commands([Console\ImportStudentsCommand::class]);
        }
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
