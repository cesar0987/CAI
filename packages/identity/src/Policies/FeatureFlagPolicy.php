<?php

declare(strict_types=1);

namespace Cai\Identity\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\FeatureFlag;
use Cai\Identity\Models\User;

/**
 * FeatureFlagPolicy — kill switches (06.04). Ver y accionar requieren
 * identity.flags.manage. Los flags los define FeatureFlagCatalog y los
 * siembra IdentitySeeder: no se crean ni se borran desde la UI.
 */
final class FeatureFlagPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'identity.flags';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function view(User $user, FeatureFlag $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, FeatureFlag $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function delete(User $user, FeatureFlag $model): bool
    {
        return false;
    }

    public function restore(User $user, FeatureFlag $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, FeatureFlag $model): bool
    {
        return false;
    }
}
