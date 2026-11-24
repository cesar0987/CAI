<?php

declare(strict_types=1);

namespace Cai\Billing\Models;

use Cai\Core\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** billing.payment_allocations — 03.06 §6. @property string $amount */
final class PaymentAllocation extends Model
{
    protected string $schema = 'billing';

    protected $table = 'payment_allocations';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['payment_id', 'charge_id', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    /** @return BelongsTo<Payment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** @return BelongsTo<Charge, $this> */
    public function charge(): BelongsTo
    {
        return $this->belongsTo(Charge::class);
    }
}
