<?php

declare(strict_types=1);

namespace Cai\Identity\Authorization;

use Cai\Identity\Users\UserAccount;

final class EffectivePermissions
{
    /**
     * @return array<string, Permission>
     */
    public static function fromUser(UserAccount $user): array
    {
        $effective = [];

        foreach ($user->roles as $roleName => $assignment) {
            if (! $assignment->isActive()) {
                continue;
            }

            $role = RoleCatalog::all()[$roleName] ?? null;

            if ($role === null) {
                continue;
            }

            foreach ($role->permissions as $permission) {
                $effective[$permission->name] = $permission;
            }
        }

        return $effective;
    }

    public static function has(UserAccount $user, string $permissionName): bool
    {
        $permissionName = trim($permissionName);

        foreach (self::fromUser($user) as $permission) {
            if ($permission->matches($permissionName)) {
                return true;
            }
        }

        return false;
    }
}
