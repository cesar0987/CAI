<?php

declare(strict_types=1);

namespace Cai\Identity\Authorization;

final readonly class Role
{
    /**
     * @param  array<string, Permission>  $permissions
     */
    public function __construct(
        public string $name,
        public array $permissions = [],
    ) {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('El nombre del rol no puede estar vacío.');
        }
    }

    public function hasPermission(string $permissionName): bool
    {
        foreach ($this->permissions as $permission) {
            if ($permission->matches($permissionName)) {
                return true;
            }
        }

        return false;
    }
}
