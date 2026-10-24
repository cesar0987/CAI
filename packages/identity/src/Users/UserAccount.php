<?php

declare(strict_types=1);

namespace Cai\Identity\Users;

final readonly class UserAccount
{
    /**
     * @param array<string, UserRoleAssignment> $roles
     * @param array<int> $studentIds
     */
    public function __construct(
        public int $id,
        public string $email,
        public string $name,
        public UserStatus $status,
        public array $roles = [],
        public array $studentIds = [],
    ) {
        if (trim($email) === '') {
            throw new \InvalidArgumentException('El email del usuario no puede estar vacío.');
        }

        if (trim($name) === '') {
            throw new \InvalidArgumentException('El nombre del usuario no puede estar vacío.');
        }
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    public function hasRole(string $role): bool
    {
        foreach ($this->roles as $assignment) {
            if ($assignment->role === trim($role) && $assignment->isActive()) {
                return true;
            }
        }

        return false;
    }

    public function canAccessStudent(int $studentId): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($this->hasRole('direccion') || $this->hasRole('admin_tecnico')) {
            return true;
        }

        return in_array($studentId, $this->studentIds, true);
    }
}
