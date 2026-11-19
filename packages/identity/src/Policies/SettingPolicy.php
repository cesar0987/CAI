<?php

declare(strict_types=1);

namespace Cai\Identity\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\Setting;
use Cai\Identity\Models\User;

/**
 * SettingPolicy — configuración del centro (03.01 §5).
 * Ver y editar requieren identity.settings.manage. Los settings no se
 * borran: se desactivan o se sobrescriben (el histórico es el activo).
 */
final class SettingPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'identity.settings';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function view(User $user, Setting $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function update(User $user, Setting $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function delete(User $user, Setting $model): bool
    {
        return false;
    }

    public function restore(User $user, Setting $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Setting $model): bool
    {
        return false;
    }
}
