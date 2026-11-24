<?php

declare(strict_types=1);

namespace Cai\Care\Policies;

use Cai\Care\Models\CareGoal;
use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;

final class CareGoalPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'care.goals';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function view(User $user, CareGoal $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function update(User $user, CareGoal $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function delete(User $user, CareGoal $model): bool
    {
        return false;
    }
}
