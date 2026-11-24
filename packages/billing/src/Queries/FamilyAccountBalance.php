<?php

declare(strict_types=1);

namespace Cai\Billing\Queries;

use Cai\Core\Support\ModuleConnection;
use Illuminate\Support\Facades\DB;

/**
 * Saldo de la familia calculado, no almacenado (03.06 §2 / ADR-0007 §2.5).
 *
 * La trampa del LEFT JOIN (08.08 §3.5): SUM(c.amount) conta un cargo tres veces
 * si tiene tres imputaciones. Se agrega primero en una subconsulta.
 */
final class FamilyAccountBalance
{
    public function forFamily(int $familyAccountId): FamilyAccountBalanceRow
    {
        $charges = DB::getDriverName() === 'pgsql' ? 'billing.charges' : 'charges';
        $allocations = DB::getDriverName() === 'pgsql' ? 'billing.payment_allocations' : 'payment_allocations';

        $row = DB::connection(ModuleConnection::for('billing'))->selectOne(
            <<<SQL
            SELECT
                COALESCE(SUM(c.amount), 0)   AS charged,
                COALESCE(SUM(alloc.total), 0) AS allocated
            FROM {$charges} c
            LEFT JOIN (
                SELECT charge_id, SUM(amount) AS total
                FROM {$allocations}
                GROUP BY charge_id
            ) alloc ON alloc.charge_id = c.id
            WHERE c.family_account_id = ?
              AND c.status <> 'voided'
            SQL,
            [$familyAccountId],
        );

        $charged = bcadd((string) ($row->charged ?? '0'), '0', 2);
        $allocated = bcadd((string) ($row->allocated ?? '0'), '0', 2);

        return new FamilyAccountBalanceRow(
            charged: $charged,
            allocated: $allocated,
            outstanding: bcsub($charged, $allocated, 2),
        );
    }
}
