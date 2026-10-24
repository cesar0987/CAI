<?php

declare(strict_types=1);

namespace Cai\Identity\Users;

final readonly class UserRoleAssignment
{
    public function __construct(
        public string $role,
        public bool $active = true,
    ) {
        if (trim($role) === '') {
            throw new \InvalidArgumentException('El rol del usuario no puede estar vacío.');
        }
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
