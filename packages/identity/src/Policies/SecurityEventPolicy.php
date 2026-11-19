<?php

declare(strict_types=1);

namespace Cai\Identity\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\SecurityEvent;
use Cai\Identity\Models\User;

/**
 * SecurityEventPolicy — solo lectura (03.01 §8). Ver requiere
 * identity.security_events.view (direccion y admin_tecnico).
 * Los eventos los escribe el sistema, nadie desde la UI.
 */
final class SecurityEventPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'identity.security_events';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'view');
    }

    public function view(User $user, SecurityEvent $model): bool
    {
        return $this->can($user, 'view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, SecurityEvent $model): bool
    {
        return false;
    }

    public function delete(User $user, SecurityEvent $model): bool
    {
        return false;
    }

    public function restore(User $user, SecurityEvent $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, SecurityEvent $model): bool
    {
        return false;
    }
}
