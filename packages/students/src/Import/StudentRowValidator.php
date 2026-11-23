<?php

declare(strict_types=1);

namespace Cai\Students\Import;

use Cai\Students\Models\Student;
use Cai\Students\Support\StudentCodeGenerator;

/**
 * Validación de una fila de la planilla. Los motivos son los de 08.05 §2.6;
 * `invalid_checksum` y `missing_checksum` viven en ADR-0020.
 */
final class StudentRowValidator
{
    public function __construct(private readonly StudentCodeGenerator $codes) {}

    /**
     * @param  array<string, string>  $row
     */
    public function validate(array $row): RowValidation
    {
        foreach (['codigo', 'nombre', 'apellido', 'fecha_nacimiento', 'documento'] as $field) {
            if (trim((string) ($row[$field] ?? '')) === '') {
                return RowValidation::rejected('missing_required_field');
            }
        }

        $birth = (string) $row['fecha_nacimiento'];
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $birth);

        if ($parsed === false || $parsed->format('Y-m-d') !== $birth) {
            return RowValidation::rejected('invalid_date');
        }

        if ($parsed > new \DateTimeImmutable) {
            return RowValidation::rejected('future_birth_date');
        }

        foreach (['email_tutor', 'email_tutor_2'] as $emailField) {
            $email = trim((string) ($row[$emailField] ?? ''));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                return RowValidation::rejected('invalid_email');
            }
        }

        $code = trim((string) $row['codigo']);
        if (! $this->codes->isValid($code)) {
            return RowValidation::rejected(
                preg_match(StudentCodeGenerator::CODE_PATTERN, $code) === 1
                    ? 'invalid_checksum'
                    : 'missing_checksum',
            );
        }

        if (Student::withTrashed()->where('student_code', $code)->exists()) {
            return RowValidation::rejected('duplicate_code');
        }

        $document = trim((string) $row['documento']);
        if (Student::withTrashed()->where('document_id', $document)->exists()) {
            return RowValidation::rejected('duplicate_document');
        }

        return RowValidation::accepted([
            'student_code' => $code,
            'first_name' => trim((string) $row['nombre']),
            'last_name' => trim((string) $row['apellido']),
            'birth_date' => $birth,
            'document_id' => $document,
            'address' => trim((string) ($row['direccion'] ?? '')),
            'phone' => trim((string) ($row['telefono'] ?? '')),
            'guardian' => [
                'first_name' => trim((string) ($row['nombre_tutor'] ?? '')),
                'last_name' => trim((string) ($row['apellido_tutor'] ?? '')),
                'document_id' => trim((string) ($row['documento_tutor'] ?? '')),
                'email' => trim((string) ($row['email_tutor'] ?? '')),
            ],
        ]);
    }
}
