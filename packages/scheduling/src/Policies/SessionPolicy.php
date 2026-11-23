<?php

declare(strict_types=1);

namespace Cai\Scheduling\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;
use Cai\Scheduling\Models\Session;

/**
 * E-08: permiso primero (04.02) y alcance por registro después (04.03 §3).
 * El listado Filament usa además `Session::visibleTo()`; acá se repite la
 * comprobación para el acceso directo a un registro por URL.
 */
final class SessionPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'scheduling.sessions';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'view');
    }

    public function view(User $user, Session $model): bool
    {
        if (! $this->can($user, 'view')) {
            return false;
        }

        return Session::visibleTo($user)->whereKey($model->id)->exists();
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create');
    }

    public function update(User $user, Session $model): bool
    {
        if (! $this->can($user, 'update')) {
            return false;
        }

        return Session::visibleTo($user)->whereKey($model->id)->exists();
    }

    public function delete(User $user, Session $model): bool
    {
        // Una sesión nunca se borra: se cancela o se reprograma.
        return false;
    }

    public function restore(User $user, Session $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Session $model): bool
    {
        return false;
    }
}
