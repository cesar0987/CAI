<?php

declare(strict_types=1);

namespace Cai\Identity\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\Role;
use Cai\Identity\Models\User;

/**
 * RolePolicy — solo Dirección gestiona roles (04.02: identity.roles.manage
 * es exclusivo de direccion; admin_tecnico NO lo tiene).
 * Los 7 roles canónicos no se borran: su permisos se ajustan, no se eliminan.
 */
final class RolePolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'identity.roles';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function view(User $user, Role $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function update(User $user, Role $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function delete(User $user, Role $model): bool
    {
        // Los roles del catálogo canónico no se borran (04.02 §1)
        return false;
    }

    public function restore(User $user, Role $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Role $model): bool
    {
        return false;
    }
}
