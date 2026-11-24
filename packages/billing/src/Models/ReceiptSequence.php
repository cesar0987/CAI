<?php

declare(strict_types=1);

namespace Cai\Billing\Models;

use Cai\Core\Models\Model;

/** billing.receipt_sequences — 03.06 §7. */
final class ReceiptSequence extends Model
{
    protected string $schema = 'billing';

    protected $table = 'receipt_sequences';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['year', 'last_number', 'prefix'];
}
