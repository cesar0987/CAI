<?php

declare(strict_types=1);

namespace Cai\Identity\Authorization;

final readonly class Permission
{
    public function __construct(
        public string $name,
        public string $module,
        public bool $sensitive = false,
        public string $description = '',
    ) {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('El nombre del permiso no puede estar vacío.');
        }

        if (trim($module) === '') {
            throw new \InvalidArgumentException('El módulo del permiso no puede estar vacío.');
        }
    }

    public function matches(string $permissionName): bool
    {
        return $this->name === trim($permissionName);
    }
}
