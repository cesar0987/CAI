<?php

declare(strict_types=1);

namespace Cai\Billing\Models;

use Cai\Core\Models\Model;
use Cai\Scheduling\Models\Service;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** billing.fee_plans — 03.06 §3. */
final class FeePlan extends Model
{
    protected string $schema = 'billing';

    protected $table = 'fee_plans';

    /** @var list<string> */
    protected $fillable = [
        'service_id', 'name', 'description', 'amount', 'periodicity', 'sessions_included',
        'discount_percent', 'is_active', 'valid_from', 'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'valid_from' => 'immutable_date',
            'valid_until' => 'immutable_date',
        ];
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return HasMany<Charge, $this> */
    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class);
    }
}
