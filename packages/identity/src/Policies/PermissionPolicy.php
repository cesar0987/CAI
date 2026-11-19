<?php

declare(strict_types=1);

namespace Cai\Identity\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\Permission;
use Cai\Identity\Models\User;

/**
 * PermissionPolicy — el catálogo de permisos es de solo lectura:
 * lo define la matriz 04.02 y lo siembra IdentitySeeder. Verlo requiere
 * identity.permissions.view; nadie lo edita desde la UI.
 */
final class PermissionPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'identity.permissions';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'view');
    }

    public function view(User $user, Permission $model): bool
    {
        return $this->can($user, 'view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Permission $model): bool
    {
        return false;
    }

    public function delete(User $user, Permission $model): bool
    {
        return false;
    }

    public function restore(User $user, Permission $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Permission $model): bool
    {
        return false;
    }
}
