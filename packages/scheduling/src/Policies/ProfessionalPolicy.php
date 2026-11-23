<?php

declare(strict_types=1);

namespace Cai\Scheduling\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;
use Cai\Scheduling\Models\Professional;

/**
 * 04.02 solo define `scheduling.professionals.manage`: lectura y escritura
 * comparten la misma puerta (no se creó un permiso nuevo).
 */
final class ProfessionalPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'scheduling.professionals';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function view(User $user, Professional $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function update(User $user, Professional $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function delete(User $user, Professional $model): bool
    {
        return false;
    }

    public function restore(User $user, Professional $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Professional $model): bool
    {
        return false;
    }
}
