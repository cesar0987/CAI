<?php

declare(strict_types=1);

namespace Cai\Identity;

use Cai\Identity\Models\AuditEvent;
use Cai\Identity\Models\FeatureFlag;
use Cai\Identity\Models\Permission;
use Cai\Identity\Models\Role;
use Cai\Identity\Models\SecurityEvent;
use Cai\Identity\Models\Setting;
use Cai\Identity\Models\User;
use Cai\Identity\Policies\AuditEventPolicy;
use Cai\Identity\Policies\FeatureFlagPolicy;
use Cai\Identity\Policies\PermissionPolicy;
use Cai\Identity\Policies\RolePolicy;
use Cai\Identity\Policies\SecurityEventPolicy;
use Cai\Identity\Policies\SettingPolicy;
use Cai\Identity\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([Console\CreateUserCommand::class]);
        }
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'identity');

        // Policies de identity (F2 E-05). Sin Gate::before: nadie tiene
        // bypass; cada rol abre exactamente las puertas de la matriz 04.02.
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(AuditEvent::class, AuditEventPolicy::class);
        Gate::policy(SecurityEvent::class, SecurityEventPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(FeatureFlag::class, FeatureFlagPolicy::class);
    }
}
