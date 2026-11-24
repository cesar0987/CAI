<?php

declare(strict_types=1);

namespace Cai\Billing\Models;

use Cai\Core\Models\Model;
use Cai\Identity\Models\User;
use Cai\Students\Models\FamilyAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** billing.financial_adjustments — 03.06 §9. */
final class FinancialAdjustment extends Model
{
    protected string $schema = 'billing';

    protected $table = 'financial_adjustments';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'charge_id', 'family_account_id', 'kind', 'amount', 'reason',
        'authorized_by', 'created_by', 'reversed_adjustment_id',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    /** @return BelongsTo<Charge, $this> */
    public function charge(): BelongsTo
    {
        return $this->belongsTo(Charge::class);
    }

    /** @return BelongsTo<FamilyAccount, $this> */
    public function family(): BelongsTo
    {
        return $this->belongsTo(FamilyAccount::class, 'family_account_id');
    }

    /** @return BelongsTo<User, $this> */
    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    /** @return BelongsTo<User, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<FinancialAdjustment, $this> */
    public function reversed(): BelongsTo
    {
        return $this->belongsTo(FinancialAdjustment::class, 'reversed_adjustment_id');
    }
}
