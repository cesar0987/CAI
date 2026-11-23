<?php

declare(strict_types=1);

namespace Cai\Students\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;
use Cai\Students\Models\Guardian;

/**
 * Tutores (04.02): el catálogo define `students.guardians.manage` y no un
 * permiso de lectura separado. Recepción puede gestionar contactos sin tener
 * acceso al listado de alumnos (04.02 §6).
 */
final class GuardianPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'students.guardians';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function view(User $user, Guardian $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function update(User $user, Guardian $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function delete(User $user, Guardian $model): bool
    {
        return false;
    }

    public function restore(User $user, Guardian $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Guardian $model): bool
    {
        return false;
    }
}
