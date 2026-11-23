<?php

declare(strict_types=1);

use Cai\Identity\Models\Role;
use Cai\Identity\Models\User;
use Cai\Students\Models\AuthorizedPickup;
use Cai\Students\Models\FamilyAccount;
use Cai\Students\Models\FamilyAccountMember;
use Cai\Students\Models\Guardian;
use Cai\Students\Models\Student;
use Cai\Students\Support\StudentCodeGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates the six students tables plus the code-sequence support table', function () {
    foreach ([
        'students', 'guardians', 'student_guardians', 'family_accounts',
        'family_account_members', 'authorized_pickups', 'student_code_sequences',
    ] as $table) {
        expect(Schema::hasTable($table))->toBeTrue("falta la tabla {$table}");
    }
});

it('generates stable ALU-AAAA-NNNN-C codes with a per-year Luhn checksum', function () {
    $generator = app(StudentCodeGenerator::class);

    $first = $generator->generate(2026);
    $second = $generator->generate(2026);
    $otherYear = $generator->generate(2027);

    expect($first)->toBe('ALU-2026-0001-1')
        ->and($second)->toBe('ALU-2026-0002-9')
        ->and($otherYear)->toBe('ALU-2027-0001-9')
        ->and($generator->isValid('ALU-2026-0042-5'))->toBeTrue()
        ->and($generator->isValid('ALU-2026-0042-6'))->toBeFalse()
        ->and($generator->isValid('ALU-2026-0024-5'))->toBeFalse()
        ->and($generator->isValid('ALU-2026-0042'))->toBeFalse()
        ->and($generator->isValid('alu-2026-0042-5'))->toBeFalse();
});

it('reuses a valid preferred code without consuming the sequence and rejects a bad checksum', function () {
    $generator = app(StudentCodeGenerator::class);

    expect($generator->generate(2026, 'ALU-2024-0007-3'))->toBe('ALU-2024-0007-3')
        ->and($generator->generate(2026))->toBe('ALU-2026-0001-1');

    expect(fn () => $generator->generate(2026, 'ALU-2024-0007'))
        ->toThrow(InvalidArgumentException::class);
});

it('enforces unique student codes and document ids', function () {
    $student = Student::factory()->create();

    expect(fn () => Student::factory()->create(['student_code' => $student->student_code]))
        ->toThrow(QueryException::class);
});

it('allows two siblings in the same family with separate records', function () {
    $family = FamilyAccount::factory()->create();
    $a = Student::factory()->create();
    $b = Student::factory()->create();

    FamilyAccountMember::factory()->create(['family_account_id' => $family->id, 'student_id' => $a->id]);
    FamilyAccountMember::factory()->create(['family_account_id' => $family->id, 'student_id' => $b->id]);

    expect($family->fresh()->students)->toHaveCount(2)
        ->and($a->id)->not->toBe($b->id)
        ->and($a->student_code)->not->toBe($b->student_code);
});

it('allows only one primary guardian per student', function () {
    $student = Student::factory()->create();
    $primary = Guardian::factory()->create();
    $second = Guardian::factory()->create();

    DB::table('student_guardians')->insert([
        'student_id' => $student->id, 'guardian_id' => $primary->id,
        'relationship' => 'Madre', 'is_primary' => true,
        'valid_from' => now()->toDateString(),
    ]);

    expect(fn () => DB::table('student_guardians')->insert([
        'student_id' => $student->id, 'guardian_id' => $second->id,
        'relationship' => 'Padre', 'is_primary' => true,
        'valid_from' => now()->toDateString(),
    ]))->toThrow(QueryException::class);

    expect($student->fresh()->guardians)->toHaveCount(1);
});

it('keeps membership history when a student is transferred between accounts', function () {
    $student = Student::factory()->create();
    $old = FamilyAccount::factory()->create();
    $new = FamilyAccount::factory()->create();

    $membership = FamilyAccountMember::factory()->create([
        'family_account_id' => $old->id, 'student_id' => $student->id,
    ]);

    // La transferencia cierra la membresía anterior: no borra la fila.
    $membership->update(['valid_until' => now()->toDateString()]);
    FamilyAccountMember::factory()->create([
        'family_account_id' => $new->id, 'student_id' => $student->id,
        'valid_from' => now()->toDateString(),
    ]);

    expect($student->familyAccountMemberships()->count())->toBe(2)
        ->and($student->familyAccountMemberships()->whereNull('valid_until')->count())->toBe(1)
        ->and($student->familyAccountMemberships()->whereNotNull('valid_until')->count())->toBe(1);
});

it('registers an authorized third party with document and validity', function () {
    $student = Student::factory()->create();

    $pickup = AuthorizedPickup::factory()->create([
        'student_id' => $student->id,
        'document_id' => '1234567-X',
    ]);

    expect($pickup->isValid())->toBeTrue()
        ->and($student->authorizedPickups)->toHaveCount(1);

    $pickup->update(['valid_until' => now()->subDay()->toDateString()]);
    expect($pickup->fresh()->isValid())->toBeFalse();
});

it('derives age from birth date and hides withdrawn students from active listings', function () {
    $student = Student::factory()->create([
        'birth_date' => now()->subYears(8)->subDay()->toDateString(),
    ]);

    expect($student->age())->toBe(8);

    $student->update(['status' => 'withdrawn']);
    expect(Student::query()->where('status', 'active')->count())->toBe(0);
});

it('applies the visibility scope: global roles see all, scoped roles see none until F4', function () {
    Role::create(['name' => 'direccion', 'label' => 'Dirección']);
    $student = Student::factory()->create();

    $direccion = User::factory()->create(['password_changed_at' => now()]);
    $direccion->assignRole('direccion');

    $sinRol = User::factory()->create(['password_changed_at' => now()]);

    expect(Student::query()->visibleTo($direccion->fresh())->count())->toBe(1)
        ->and(Student::query()->visibleTo($sinRol)->count())->toBe(0)
        ->and(Student::query()->visibleTo(null)->count())->toBe(0);
});
