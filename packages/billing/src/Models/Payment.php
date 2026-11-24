<?php

declare(strict_types=1);

namespace Cai\Billing\Models;

use Cai\Core\Models\Model;
use Cai\Identity\Models\User;
use Cai\Students\Models\FamilyAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * billing.payments — 03.06 §5.
 *
 * @property int $family_account_id
 * @property string $amount
 * @property string $status
 */
final class Payment extends Model
{
    protected string $schema = 'billing';

    protected $table = 'payments';

    /** @var list<string> */
    protected $fillable = [
        'family_account_id', 'cash_session_id', 'amount', 'paid_at', 'method', 'reference',
        'payer_name', 'status', 'received_by', 'void_reason', 'voided_at', 'voided_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'immutable_datetime',
            'voided_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<FamilyAccount, $this> */
    public function family(): BelongsTo
    {
        return $this->belongsTo(FamilyAccount::class, 'family_account_id');
    }

    /** @return BelongsTo<CashSession, $this> */
    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    /** @return BelongsTo<User, $this> */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /** @return HasMany<PaymentAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /** @return HasOne<Receipt, $this> */
    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class);
    }
}
