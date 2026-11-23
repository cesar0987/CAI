<?php

declare(strict_types=1);

namespace Cai\Students\Policies;

use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;
use Cai\Students\Models\FamilyAccount;

/**
 * Cuentas familiares (ADR-0007): lectura y gestión con permisos distintos.
 * El saldo no vive en F3: se calcula en F6.
 */
final class FamilyAccountPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'students.family_accounts';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'view');
    }

    public function view(User $user, FamilyAccount $model): bool
    {
        return $this->can($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'manage');
    }

    public function update(User $user, FamilyAccount $model): bool
    {
        return $this->can($user, 'manage');
    }

    public function delete(User $user, FamilyAccount $model): bool
    {
        return false;
    }

    public function restore(User $user, FamilyAccount $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, FamilyAccount $model): bool
    {
        return false;
    }
}
