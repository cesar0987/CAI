<?php

declare(strict_types=1);

use Cai\Billing\Models\Charge;
use Cai\Billing\Models\Payment;
use Cai\Billing\Models\PaymentAllocation;
use Cai\Billing\Queries\FamilyAccountBalance;
use Cai\Billing\Support\Money;
use Cai\Identity\Models\User;
use Cai\Students\Models\FamilyAccount;
use Cai\Students\Models\FamilyAccountMember;
use Cai\Students\Models\Student;
use Illuminate\Support\Facades\Schema;

function billingFamily(): FamilyAccount
{
    return FamilyAccount::factory()->create();
}

function billingStudent(FamilyAccount $family): Student
{
    $student = Student::factory()->create();
    FamilyAccountMember::create([
        'family_account_id' => $family->id,
        'student_id' => $student->id,
        'role' => 'child',
        'valid_from' => now()->subYear()->toDateString(),
    ]);

    return $student;
}

function billingCharge(FamilyAccount $family, Student $student, string $amount): Charge
{
    return Charge::create([
        'family_account_id' => $family->id,
        'student_id' => $student->id,
        'description' => 'Cuota mensual',
        'due_date' => now()->addDays(10)->toDateString(),
        'amount' => $amount,
    ]);
}

function billingPayment(FamilyAccount $family, string $amount): Payment
{
    $user = User::factory()->create();

    return Payment::create([
        'family_account_id' => $family->id,
        'amount' => $amount,
        'paid_at' => now(),
        'method' => 'cash',
        'received_by' => $user->id,
    ]);
}

it('creates the eight billing tables defined in 03.06', function () {
    foreach (['fee_plans', 'cash_sessions', 'charges', 'payments', 'payment_allocations', 'receipt_sequences', 'receipts', 'financial_adjustments'] as $table) {
        expect(Schema::hasTable($table))->toBeTrue($table);
    }
});

it('handles amounts as exact decimals and only rounds on presentation', function () {
    expect(Money::parse('0.1')->plus(Money::parse('0.2'))->value)->toBe('0.30')
        ->and(Money::parse('37500')->format())->toBe('37.500,00 Gs.');

    config(['cai.finanzas.rounding_mode' => '1000']);
    expect(Money::parse('37500')->format())->toBe('38.000,00 Gs.')
        ->and(Money::parse('37500')->value)->toBe('37500.00');
});

it('does not double count a charge with three allocations', function () {
    $family = billingFamily();
    $charge = billingCharge($family, billingStudent($family), '100000.00');

    // Tres pagos distintos sobre el mismo cargo (el unique es payment_id + charge_id).
    foreach (['10000.00', '10000.00', '10000.00'] as $amount) {
        $payment = billingPayment($family, $amount);
        PaymentAllocation::create([
            'payment_id' => $payment->id,
            'charge_id' => $charge->id,
            'amount' => $amount,
        ]);
    }

    $balance = app(FamilyAccountBalance::class)->forFamily($family->id);

    expect($balance->charged)->toBe('100000.00')
        ->and($balance->allocated)->toBe('30000.00')
        ->and($balance->outstanding)->toBe('70000.00');
});

it('computes the family balance across two siblings from a single payment', function () {
    $family = billingFamily();
    $first = billingCharge($family, billingStudent($family), '300000.00');
    $second = billingCharge($family, billingStudent($family), '200000.00');
    $payment = billingPayment($family, '500000.00');

    PaymentAllocation::create(['payment_id' => $payment->id, 'charge_id' => $first->id, 'amount' => '300000.00']);
    PaymentAllocation::create(['payment_id' => $payment->id, 'charge_id' => $second->id, 'amount' => '200000.00']);

    $balance = app(FamilyAccountBalance::class)->forFamily($family->id);

    expect($balance->outstanding)->toBe('0.00')
        ->and($balance->charged)->toBe('500000.00');
});
