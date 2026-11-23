<?php

declare(strict_types=1);

namespace Cai\Students\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;
use Cai\Students\Models\Student;

/**
 * Ficha del alumno: permisos canónicos de 04.02 (E-04).
 * Nunca se borra una ficha: se da de baja con `status`.
 */
final class StudentPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'students.students';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'view');
    }

    public function view(User $user, Student $model): bool
    {
        return $this->can($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create');
    }

    public function update(User $user, Student $model): bool
    {
        return $this->can($user, 'update');
    }

    public function delete(User $user, Student $model): bool
    {
        return false;
    }

    public function restore(User $user, Student $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Student $model): bool
    {
        return false;
    }
}
