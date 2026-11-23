<?php

declare(strict_types=1);

namespace Cai\Students\Contracts;

use Cai\Students\Enums\StudentStatus;

final readonly class StudentSummary
{
    public function __construct(
        public int $id,
        public string $code,
        public string $firstName,
        public string $lastName,
        public \DateTimeImmutable $birthDate,
        public StudentStatus $status,
        public ?int $familyAccountId,
        public ?string $primaryGuardianName,
    ) {}

    public function fullName(): string
    {
        return trim("{$this->firstName} {$this->lastName}");
    }

    public function age(): int
    {
        return $this->birthDate->diff(new \DateTimeImmutable)->y;
    }
}
