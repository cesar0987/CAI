<?php

declare(strict_types=1);

use Cai\Students\Actions\ImportStudents;
use Cai\Students\Actions\TransferFamilyMembership;
use Cai\Students\Contracts\StudentDirectory;
use Cai\Students\Data\ImportStudentsData;
use Cai\Students\Enums\StudentStatus;
use Cai\Students\Models\FamilyAccount;
use Cai\Students\Models\FamilyAccountMember;
use Cai\Students\Models\Guardian;
use Cai\Students\Models\Student;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/** @var list<string> $csvFiles */
$csvFiles = [];

$makeCsv = function (array $rows) use (&$csvFiles): string {
    $path = tempnam(sys_get_temp_dir(), 'cai-students-');

    if ($path === false) {
        throw new RuntimeException('No se pudo crear el CSV temporal de la prueba.');
    }

    $handle = fopen($path, 'wb');

    if ($handle === false) {
        throw new RuntimeException('No se pudo abrir el CSV temporal de la prueba.');
    }

    fputcsv($handle, [
        'codigo', 'nombre', 'apellido', 'fecha_nacimiento', 'documento',
        'direccion', 'telefono', 'nombre_tutor', 'apellido_tutor',
        'documento_tutor', 'email_tutor', 'email_tutor_2',
    ]);

    foreach ($rows as $row) {
        fputcsv($handle, $row);
    }

    fclose($handle);
    $csvFiles[] = $path;

    return $path;
};

afterEach(function () use (&$csvFiles): void {
    foreach ($csvFiles as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }

    $csvFiles = [];
});

$validRow = fn (string $code = 'ALU-2026-0042-5'): array => [
    $code, 'Ana', 'Pérez', '2018-05-10', '1111111',
    'Calle 1', '021111111', 'María', 'Pérez', '2222222', 'maria@example.test', '',
];

it('exposes student summaries through the public directory contract', function () {
    $student = Student::factory()->create([
        'first_name' => 'Ana',
        'last_name' => 'Pérez',
        'document_id' => '1111111',
    ]);
    $family = FamilyAccount::factory()->create();
    FamilyAccountMember::factory()->create([
        'family_account_id' => $family->id,
        'student_id' => $student->id,
    ]);

    $guardian = Guardian::factory()->create(['first_name' => 'María', 'last_name' => 'Pérez']);
    DB::table('student_guardians')->insert([
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
        'relationship' => 'Madre',
        'is_primary' => true,
        'valid_from' => now()->toDateString(),
    ]);

    $directory = app(StudentDirectory::class);
    $summary = $directory->findByCode($student->student_code);

    expect($summary)->not->toBeNull()
        ->and($summary->fullName())->toBe('Ana Pérez')
        ->and($summary->status)->toBe(StudentStatus::Active)
        ->and($summary->familyAccountId)->toBe($family->id)
        ->and($summary->primaryGuardianName)->toBe('María Pérez')
        ->and($directory->findByDocument('1111111'))->toHaveCount(1)
        ->and($directory->find(999999))->toBeNull();
});

it('dry-runs the importer by default without writing students or audit rows', function () use ($makeCsv, $validRow) {
    $path = $makeCsv([$validRow()]);
    $auditBefore = DB::table('audit_events')->count();

    Artisan::call('cai:import:students', ['--file' => $path]);

    expect(Artisan::output())->toContain('Simulación: 1 filas aceptadas, 0 rechazadas.')
        ->and(Student::count())->toBe(0)
        ->and(FamilyAccount::count())->toBe(0)
        ->and(DB::table('audit_events')->count())->toBe($auditBefore);
});

it('imports a valid row with guardian, family account and membership when execute is passed', function () use ($makeCsv, $validRow) {
    $path = $makeCsv([$validRow()]);

    Artisan::call('cai:import:students', ['--file' => $path, '--execute' => true]);

    expect(Artisan::output())->toContain('Importación: 1 filas aceptadas, 0 rechazadas.')
        ->and(Student::count())->toBe(1)
        ->and(Guardian::count())->toBe(1)
        ->and(FamilyAccount::count())->toBe(1)
        ->and(FamilyAccountMember::count())->toBe(1)
        ->and(Student::first()->student_code)->toBe('ALU-2026-0042-5')
        ->and((int) DB::table('student_guardians')->first()->is_primary)->toBe(1)
        ->and(DB::table('audit_events')->where('action', 'students.imported')->exists())->toBeTrue();
});

it('rejects codes with a missing or invalid checksum and reports the reason', function () use ($makeCsv, $validRow) {
    $path = $makeCsv([
        $validRow('ALU-2026-0042'),
        $validRow('ALU-2026-0042-6'),
    ]);

    $result = app(ImportStudents::class)->execute(new ImportStudentsData($path));

    expect($result->accepted)->toBe(0)
        ->and(array_column($result->rejected, 'reason'))->toBe(['missing_checksum', 'invalid_checksum'])
        ->and(Student::count())->toBe(0);
});

it('rejects duplicate codes inside the same spreadsheet', function () use ($makeCsv, $validRow) {
    $path = $makeCsv([
        $validRow(),
        $validRow(),
    ]);

    $result = app(ImportStudents::class)->execute(new ImportStudentsData($path));

    expect($result->accepted)->toBe(1)
        ->and(array_column($result->rejected, 'reason'))->toBe(['duplicate_code'])
        ->and(Student::count())->toBe(0);
});

it('transfers a student between accounts closing the old membership and recording the event', function () {
    $student = Student::factory()->create();
    $old = FamilyAccount::factory()->create();
    $new = FamilyAccount::factory()->create();

    $membership = FamilyAccountMember::factory()->create([
        'family_account_id' => $old->id,
        'student_id' => $student->id,
    ]);

    app(TransferFamilyMembership::class)->execute($student->id, $new->id, 'Reunificación familiar');

    expect($student->familyAccountMemberships()->count())->toBe(2)
        ->and($student->familyAccountMemberships()->whereNull('valid_until')->count())->toBe(1)
        ->and($student->familyAccountMemberships()->whereNotNull('valid_until')->count())->toBe(1)
        ->and($membership->fresh()->valid_until)->not->toBeNull()
        ->and(DB::table('audit_events')
            ->where('action', 'students.transferred_to_account')
            ->where('student_id', $student->id)
            ->where('severity', 'warning')
            ->exists())->toBeTrue();
});

it('refuses to transfer a student to the account they already belong to', function () {
    $student = Student::factory()->create();
    $account = FamilyAccount::factory()->create();
    FamilyAccountMember::factory()->create([
        'family_account_id' => $account->id,
        'student_id' => $student->id,
    ]);

    expect(fn () => app(TransferFamilyMembership::class)->execute($student->id, $account->id))
        ->toThrow(InvalidArgumentException::class);
});
