<?php

declare(strict_types=1);

namespace Cai\Identity\Authorization;

use Cai\Identity\Users\UserAccount;

final class ImpersonationPolicy
{
    /**
     * @param array<string, bool> $allowedRoles
     */
    public function __construct(
        private readonly array $allowedRoles = ['direccion', 'admin_tecnico'],
    ) {
    }

    public function canImpersonate(UserAccount $actor, UserAccount $target): bool
    {
        if (! $actor->isActive() || ! $target->isActive()) {
            return false;
        }

        if ($actor->id === $target->id) {
            return false;
        }

        foreach ($this->allowedRoles as $role) {
            if ($actor->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}
