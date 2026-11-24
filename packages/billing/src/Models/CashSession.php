<?php

declare(strict_types=1);

namespace Cai\Billing\Models;

use Cai\Core\Models\Model;
use Cai\Identity\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** billing.cash_sessions — 03.06 §8. */
final class CashSession extends Model
{
    protected string $schema = 'billing';

    protected $table = 'cash_sessions';

    /** @var list<string> */
    protected $fillable = [
        'user_id', 'opened_at', 'closed_at', 'opening_amount', 'closing_amount',
        'expected_amount', 'difference', 'status', 'notes', 'closed_by',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'opening_amount' => 'decimal:2',
            'closing_amount' => 'decimal:2',
            'expected_amount' => 'decimal:2',
            'difference' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
