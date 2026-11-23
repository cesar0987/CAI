<?php

declare(strict_types=1);

namespace Cai\Scheduling\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;
use Cai\Scheduling\Models\Enrollment;

final class EnrollmentPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'scheduling.enrollments';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'view');
    }

    public function view(User $user, Enrollment $model): bool
    {
        return $this->can($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function update(User $user, Enrollment $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function delete(User $user, Enrollment $model): bool
    {
        return false;
    }

    public function restore(User $user, Enrollment $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Enrollment $model): bool
    {
        return false;
    }
}
