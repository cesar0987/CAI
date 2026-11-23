<?php

declare(strict_types=1);

namespace Cai\Students\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;
use Cai\Students\Models\AuthorizedPickup;

/**
 * Terceros autorizados a retirar (03.02 §7). Se revocan con `is_active`,
 * nunca se borra la autorización.
 */
final class AuthorizedPickupPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'students.authorized_pickups';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function view(User $user, AuthorizedPickup $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function update(User $user, AuthorizedPickup $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function delete(User $user, AuthorizedPickup $model): bool
    {
        return false;
    }

    public function restore(User $user, AuthorizedPickup $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, AuthorizedPickup $model): bool
    {
        return false;
    }
}
