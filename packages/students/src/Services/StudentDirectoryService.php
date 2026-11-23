<?php

declare(strict_types=1);

namespace Cai\Students\Services;

use Cai\Students\Contracts\StudentDirectory;
use Cai\Students\Contracts\StudentSummary;
use Cai\Students\Enums\StudentStatus;
use Cai\Students\Models\FamilyAccountMember;
use Cai\Students\Models\Student;

/**
 * Implementación del contrato de solo lectura. Devuelve DTOs para que un
 * módulo consumidor no pueda alcanzar los atributos del modelo ni disparar
 * eventos de Eloquent ajenos (08.05 §2.4).
 */
final class StudentDirectoryService implements StudentDirectory
{
    public function find(int $id): ?StudentSummary
    {
        $student = Student::find($id);

        return $student === null ? null : $this->toSummary($student);
    }

    public function findByCode(string $code): ?StudentSummary
    {
        $student = Student::where('student_code', trim($code))->first();

        return $student === null ? null : $this->toSummary($student);
    }

    public function findByDocument(string $documentId): array
    {
        return Student::where('document_id', trim($documentId))
            ->get()
            ->map(fn (Student $student): StudentSummary => $this->toSummary($student))
            ->all();
    }

    private function toSummary(Student $student): StudentSummary
    {
        $membership = FamilyAccountMember::where('student_id', $student->id)
            ->whereNull('valid_until')
            ->first();

        $guardianName = $student->guardians()
            ->wherePivot('is_primary', true)
            ->wherePivot('valid_until', null)
            ->first()?->fullName();

        return new StudentSummary(
            $student->id,
            $student->student_code,
            $student->first_name,
            $student->last_name,
            \DateTimeImmutable::createFromInterface($student->birth_date ?? now()->toImmutable()),
            StudentStatus::from($student->status),
            $membership?->family_account_id,
            $guardianName,
        );
    }
}
