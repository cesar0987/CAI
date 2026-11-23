<?php

declare(strict_types=1);

namespace Cai\Scheduling\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;
use Cai\Scheduling\Models\AttendanceRecord;

/**
 * Registrar y corregir son permisos distintos en 04.02. Nunca se borra un
 * registro de asistencia: la corrección actualiza y deja auditoría.
 */
final class AttendancePolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'scheduling.attendance';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'record');
    }

    public function view(User $user, AttendanceRecord $model): bool
    {
        return $this->can($user, 'record');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'record');
    }

    public function update(User $user, AttendanceRecord $model): bool
    {
        return $this->can($user, 'correct');
    }

    public function delete(User $user, AttendanceRecord $model): bool
    {
        return false;
    }

    public function restore(User $user, AttendanceRecord $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, AttendanceRecord $model): bool
    {
        return false;
    }
}
