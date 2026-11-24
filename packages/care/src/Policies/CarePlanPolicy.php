<?php

declare(strict_types=1);

namespace Cai\Care\Policies;

use Cai\Care\Models\CarePlan;
use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;

final class CarePlanPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'care.plans';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'view');
    }

    public function view(User $user, CarePlan $model): bool
    {
        return $this->can($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function update(User $user, CarePlan $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function delete(User $user, CarePlan $model): bool
    {
        return false;
    }
}
