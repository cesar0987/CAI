<?php

declare(strict_types=1);

namespace Cai\Scheduling\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;
use Cai\Scheduling\Models\Group;

final class GroupPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'scheduling.groups';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function view(User $user, Group $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function update(User $user, Group $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function delete(User $user, Group $model): bool
    {
        return false;
    }

    public function restore(User $user, Group $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Group $model): bool
    {
        return false;
    }
}
