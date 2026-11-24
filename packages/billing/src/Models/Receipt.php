<?php

declare(strict_types=1);

namespace Cai\Billing\Models;

use Cai\Core\Models\Model;
use Cai\Identity\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** billing.receipts — 03.06 §7: un recibo por pago. */
final class Receipt extends Model
{
    protected string $schema = 'billing';

    protected $table = 'receipts';

    /** @var list<string> */
    protected $fillable = [
        'payment_id', 'receipt_number', 'issued_at', 'status', 'void_reason', 'voided_at',
        'issued_by', 'total',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'immutable_datetime',
            'voided_at' => 'immutable_datetime',
            'total' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Payment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** @return BelongsTo<User, $this> */
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
