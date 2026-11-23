<?php

declare(strict_types=1);

namespace Cai\Students;

use Cai\Students\Contracts\StudentDirectory;
use Cai\Students\Models\AuthorizedPickup;
use Cai\Students\Models\FamilyAccount;
use Cai\Students\Models\Guardian;
use Cai\Students\Models\Student;
use Cai\Students\Policies\AuthorizedPickupPolicy;
use Cai\Students\Policies\FamilyAccountPolicy;
use Cai\Students\Policies\GuardianPolicy;
use Cai\Students\Policies\StudentPolicy;
use Cai\Students\Services\StudentDirectoryService;
use Cai\Students\Support\StudentCodeGenerator;
use Illuminate\Support\Facades\Gate;
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

        // Policies de students (F3 E-04). Sin Gate::before: la matriz 04.02
        // abre exactamente los permisos de cada rol, sin bypass.
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Guardian::class, GuardianPolicy::class);
        Gate::policy(FamilyAccount::class, FamilyAccountPolicy::class);
        Gate::policy(AuthorizedPickup::class, AuthorizedPickupPolicy::class);
    }
}
