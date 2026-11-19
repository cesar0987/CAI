<?php

declare(strict_types=1);

namespace Cai\Identity\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;

/**
 * UserPolicy — 08.04 §2.5. Un usuario siempre ve y edita su propio
 * perfil; el resto requiere identity.users.view / .manage.
 * Nunca se borra un usuario: se desactiva (03.01 §2).
 */
final class UserPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'identity.users';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'view');
    }

    public function view(User $user, User $model): bool
    {
        // Un usuario siempre puede ver su propio perfil
        if ($user->id === $model->id) {
            return true;
        }

        return $this->can($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function update(User $user, User $model): bool
    {
        // Perfil propio: sí (los campos editables los limita el Resource)
        if ($user->id === $model->id) {
            return true;
        }

        return $this->can($user, 'manage');
    }

    public function delete(User $user, User $model): bool
    {
        // Nunca se borra un usuario: se desactiva (status = inactive)
        return false;
    }

    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
