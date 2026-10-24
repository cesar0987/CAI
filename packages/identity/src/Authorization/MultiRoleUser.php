<?php

declare(strict_types=1);

namespace Cai\Identity\Authorization;

use Cai\Identity\Users\UserAccount;

final class MultiRoleUser
{
    /**
     * @param array<string, bool> $roleFlags
     */
    public function __construct(
        private readonly UserAccount $user,
        private readonly array $roleFlags = [],
    ) {
    }

    public function hasAnyRole(string ...$roles): bool
    {
        foreach ($roles as $role) {
            if ($this->user->hasRole($role) || ($this->roleFlags[trim($role)] ?? false)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllRoles(string ...$roles): bool
    {
        foreach ($roles as $role) {
            if (! $this->user->hasRole($role) && ! ($this->roleFlags[trim($role)] ?? false)) {
                return false;
            }
        }

        return true;
    }
}
