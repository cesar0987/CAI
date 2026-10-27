<?php

declare(strict_types=1);

namespace Cai\Identity\Authorization;

use Cai\Identity\Users\UserAccount;

final class MultiRoleUser
{
    /**
     * @param  array<string, bool>  $roleFlags
     */
    public function __construct(
        private readonly UserAccount $user,
        private readonly array $roleFlags = [],
    ) {}

    public function hasRole(string $role): bool
    {
        return $this->user->hasRole($role) || ($this->roleFlags[trim($role)] ?? false);
    }

    public function hasAnyRole(string ...$roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllRoles(string ...$roles): bool
    {
        foreach ($roles as $role) {
            if (! $this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }
}
