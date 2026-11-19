<?php

declare(strict_types=1);

namespace Cai\Identity\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\AuditEvent;
use Cai\Identity\Models\User;

/**
 * AuditEventPolicy — auditoría estrictamente de lectura (04.05).
 * Ver requiere identity.audit.view (direccion, coordinacion, admin_tecnico).
 * Crear, modificar o borrar eventos: nunca, para ningún rol.
 */
final class AuditEventPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'identity.audit';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'view');
    }

    public function view(User $user, AuditEvent $model): bool
    {
        return $this->can($user, 'view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, AuditEvent $model): bool
    {
        return false;
    }

    public function delete(User $user, AuditEvent $model): bool
    {
        return false;
    }

    public function restore(User $user, AuditEvent $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, AuditEvent $model): bool
    {
        return false;
    }
}
