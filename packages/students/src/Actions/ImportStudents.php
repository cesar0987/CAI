<?php

declare(strict_types=1);

namespace Cai\Students\Actions;

use Cai\Identity\Audit\Auditor;
use Cai\Students\Data\ImportStudentsData;
use Cai\Students\Import\CsvStudentReader;
use Cai\Students\Import\StudentRowValidator;
use Cai\Students\Models\FamilyAccount;
use Cai\Students\Models\FamilyAccountMember;
use Cai\Students\Models\Guardian;
use Cai\Students\Models\Student;
use Cai\Students\Results\ImportResult;
use Cai\Students\Support\StudentCodeGenerator;
use Illuminate\Support\Facades\DB;

/**
 * Importador de la planilla actual (08.05 §2.6, 03.02 §9).
 *
 * --dry-run es el modo por defecto: valida, verifica el checksum (ADR-0020)
 * y reporta sin escribir nada, ni alumnos ni auditoría. La importación real
 * crea alumno, tutores y una cuenta familiar por alumno (Dirección decide
 * luego si unifica hermanos). Nunca pisa producción: se ejecuta en staging.
 */
final class ImportStudents
{
    public function __construct(
        private readonly CsvStudentReader $reader,
        private readonly StudentRowValidator $validator,
        private readonly Auditor $auditor,
    ) {}

    public function execute(ImportStudentsData $data): ImportResult
    {
        $accepted = 0;
        $rejected = [];
        $seenCodes = [];
        $seenDocuments = [];

        $rows = $this->reader->read($data->filePath);

        foreach ($rows as $rowNumber => $row) {
            $code = trim((string) ($row['codigo'] ?? ''));
            $document = trim((string) ($row['documento'] ?? ''));

            if ($code !== '' && isset($seenCodes[$code])) {
                $rejected[] = ['row' => $rowNumber, 'code' => $code, 'reason' => 'duplicate_code'];

                continue;
            }

            if ($document !== '' && isset($seenDocuments[$document])) {
                $rejected[] = ['row' => $rowNumber, 'code' => $code, 'reason' => 'duplicate_document'];

                continue;
            }

            $validation = $this->validator->validate($row);

            if ($validation->fails()) {
                $rejected[] = [
                    'row' => $rowNumber,
                    'code' => $code === '' ? null : $code,
                    'reason' => $validation->firstError(),
                ];

                continue;
            }

            $seenCodes[$code] = true;
            $seenDocuments[$document] = true;

            if (! $data->dryRun) {
                $this->importRow($validation->validated());
            }

            $accepted++;
        }

        if (! $data->dryRun) {
            $this->auditor->record('students.imported', Student::class, null, [
                'accepted' => $accepted,
                'rejected' => count($rejected),
            ]);
        }

        return new ImportResult($accepted, $rejected, $data->dryRun);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function importRow(array $validated): void
    {
        DB::transaction(function () use ($validated): void {
            $student = Student::create([
                'student_code' => $validated['student_code'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'birth_date' => $validated['birth_date'],
                'document_id' => $validated['document_id'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'status' => 'active',
            ]);

            $guardianData = $validated['guardian'];
            if ($guardianData['first_name'] !== '' || $guardianData['last_name'] !== '') {
                $guardian = Guardian::firstOrCreate(
                    ['document_id' => $guardianData['document_id'] !== '' ? $guardianData['document_id'] : null],
                    [
                        'first_name' => $guardianData['first_name'] !== '' ? $guardianData['first_name'] : 'Sin nombre',
                        'last_name' => $guardianData['last_name'],
                        'email' => $guardianData['email'] !== '' ? $guardianData['email'] : null,
                        'status' => 'active',
                    ],
                );

                $student->guardians()->attach($guardian->id, [
                    'relationship' => 'Tutor',
                    'is_primary' => true,
                    'receives_admin_messages' => $guardianData['email'] !== '',
                    'valid_from' => now()->toDateString(),
                ]);

                $account = FamilyAccount::create([
                    'account_code' => $student->student_code,
                    'display_name' => 'Familia '.$student->last_name,
                    'status' => 'active',
                    'currency' => 'PYG',
                    'responsible_guardian_id' => $guardian->id,
                ]);
            } else {
                $account = FamilyAccount::create([
                    'account_code' => $student->student_code,
                    'display_name' => 'Familia '.$student->last_name,
                    'status' => 'active',
                    'currency' => 'PYG',
                ]);
            }

            FamilyAccountMember::create([
                'family_account_id' => $account->id,
                'student_id' => $student->id,
                'role' => 'dependiente',
                'valid_from' => now()->toDateString(),
            ]);
        });
    }

    public function generateMissingCode(int $year): string
    {
        return app(StudentCodeGenerator::class)->generate($year);
    }
}
