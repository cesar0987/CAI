<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Última línea de defensa (03.06 §6, RN-074/RN-075): en PostgreSQL la suma de
 * imputaciones no puede superar el pago ni el cargo. En SQLite (desarrollo y
 * tests) las mismas reglas las valida la capa de aplicación.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION billing.check_payment_allocation_cap()
            RETURNS TRIGGER AS $$
            DECLARE
                v_limit NUMERIC(14,2);
                v_total NUMERIC(14,2);
            BEGIN
                IF TG_TABLE_NAME = 'payment_allocations' AND TG_ARGV[0] = 'payment' THEN
                    SELECT amount INTO v_limit FROM billing.payments WHERE id = NEW.payment_id;
                    SELECT COALESCE(SUM(amount), 0) INTO v_total
                      FROM billing.payment_allocations
                     WHERE payment_id = NEW.payment_id
                       AND (TG_OP = 'INSERT' OR id <> NEW.id);
                ELSE
                    SELECT amount INTO v_limit FROM billing.charges WHERE id = NEW.charge_id;
                    SELECT COALESCE(SUM(amount), 0) INTO v_total
                      FROM billing.payment_allocations
                     WHERE charge_id = NEW.charge_id
                       AND (TG_OP = 'INSERT' OR id <> NEW.id);
                END IF;

                IF v_total + NEW.amount > v_limit THEN
                    RAISE EXCEPTION 'Imputación (%) supera el límite (%)', v_total + NEW.amount, v_limit;
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql
            SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER payment_allocations_payment_cap
                BEFORE INSERT OR UPDATE ON billing.payment_allocations
                FOR EACH ROW EXECUTE FUNCTION billing.check_payment_allocation_cap('payment')
            SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER payment_allocations_charge_cap
                BEFORE INSERT OR UPDATE ON billing.payment_allocations
                FOR EACH ROW EXECUTE FUNCTION billing.check_payment_allocation_cap('charge')
            SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP TRIGGER IF EXISTS payment_allocations_charge_cap ON billing.payment_allocations');
        DB::statement('DROP TRIGGER IF EXISTS payment_allocations_payment_cap ON billing.payment_allocations');
        DB::statement('DROP FUNCTION IF EXISTS billing.check_payment_allocation_cap()');
    }
};
