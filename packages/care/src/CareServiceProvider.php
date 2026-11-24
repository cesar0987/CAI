<?php

declare(strict_types=1);

namespace Cai\Care;

use Cai\Care\Models\CareGoal;
use Cai\Care\Models\CarePlan;
use Cai\Care\Models\ClinicalNote;
use Cai\Care\Models\ClinicalNoteAccessGrant;
use Cai\Care\Policies\CareGoalPolicy;
use Cai\Care\Policies\CarePlanPolicy;
use Cai\Care\Policies\ClinicalNoteAccessGrantPolicy;
use Cai\Care\Policies\ClinicalNotePolicy;
use Illuminate\Support\Facades\Gate;
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

        Gate::policy(ClinicalNote::class, ClinicalNotePolicy::class);
        Gate::policy(ClinicalNoteAccessGrant::class, ClinicalNoteAccessGrantPolicy::class);
        Gate::policy(CarePlan::class, CarePlanPolicy::class);
        Gate::policy(CareGoal::class, CareGoalPolicy::class);
    }
}
