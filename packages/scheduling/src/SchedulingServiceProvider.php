<?php

declare(strict_types=1);

namespace Cai\Scheduling;

use Cai\Scheduling\Models\AttendanceRecord;
use Cai\Scheduling\Models\Enrollment;
use Cai\Scheduling\Models\Group;
use Cai\Scheduling\Models\Professional;
use Cai\Scheduling\Models\Service;
use Cai\Scheduling\Models\Session;
use Cai\Scheduling\Policies\AttendancePolicy;
use Cai\Scheduling\Policies\EnrollmentPolicy;
use Cai\Scheduling\Policies\GroupPolicy;
use Cai\Scheduling\Policies\ProfessionalPolicy;
use Cai\Scheduling\Policies\ServicePolicy;
use Cai\Scheduling\Policies\SessionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class SchedulingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([Console\GenerateSessionsCommand::class]);
        }
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'scheduling');

        // Sin Gate::before: cada rol abre exactamente las puertas de 04.02.
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(Professional::class, ProfessionalPolicy::class);
        Gate::policy(Enrollment::class, EnrollmentPolicy::class);
        Gate::policy(Group::class, GroupPolicy::class);
        Gate::policy(Session::class, SessionPolicy::class);
        Gate::policy(AttendanceRecord::class, AttendancePolicy::class);
    }
}
