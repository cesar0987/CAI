<?php

declare(strict_types=1);

namespace Cai\Students;

final readonly class Student
{
    public function __construct(
        public int $id,
        public string $fullName,
        public string $documentNumber,
        public string $status = 'active',
        public ?int $familyId = null,
    ) {
        if (trim($fullName) === '') {
            throw new \InvalidArgumentException('El nombre del alumno no puede estar vacío.');
        }

        if (trim($documentNumber) === '') {
            throw new \InvalidArgumentException('El documento del alumno no puede estar vacío.');
        }
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
