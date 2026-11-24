<?php

declare(strict_types=1);

namespace Cai\Billing\Models;

use Cai\Core\Models\Model;
use Cai\Identity\Models\User;
use Cai\Scheduling\Models\Enrollment;
use Cai\Students\Models\FamilyAccount;
use Cai\Students\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * billing.charges — 03.06 §4. No guarda saldo: se calcula desde imputaciones.
 *
 * @property int $family_account_id
 * @property int $student_id
 * @property int|null $enrollment_id
 * @property string $amount
 * @property string $amount_paid
 * @property string $status
 */
final class Charge extends Model
{
    protected string $schema = 'billing';

    protected $table = 'charges';

    /** @var list<string> */
    protected $fillable = [
        'family_account_id', 'student_id', 'enrollment_id', 'fee_plan_id', 'description',
        'period', 'due_date', 'amount', 'amount_paid', 'status', 'generated_by', 'generation_source',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'period' => 'immutable_date',
            'due_date' => 'immutable_date',
        ];
    }

    /** @return BelongsTo<FamilyAccount, $this> */
    public function family(): BelongsTo
    {
        return $this->belongsTo(FamilyAccount::class, 'family_account_id');
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<Enrollment, $this> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /** @return BelongsTo<FeePlan, $this> */
    public function feePlan(): BelongsTo
    {
        return $this->belongsTo(FeePlan::class);
    }

    /** @return BelongsTo<User, $this> */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /** @return HasMany<PaymentAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /** @return HasMany<FinancialAdjustment, $this> */
    public function adjustments(): HasMany
    {
        return $this->hasMany(FinancialAdjustment::class);
    }
}
