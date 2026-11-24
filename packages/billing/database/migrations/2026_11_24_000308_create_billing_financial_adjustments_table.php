<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * billing.financial_adjustments — 03.06 §9 (equivale a `charge_adjustments` en 08.08).
 * Un ajuste nunca borra el cargo; se revierte con otro ajuste.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        $familiesTable = $driver === 'pgsql' ? 'students.family_accounts' : 'family_accounts';
        $usersTable = $driver === 'pgsql' ? 'identity.users' : 'users';

        Schema::create('financial_adjustments', function (Blueprint $table) use ($familiesTable, $usersTable): void {
            $table->id();
            $table->foreignId('charge_id')->nullable()->constrained('charges')->cascadeOnDelete();
            $table->foreignId('family_account_id')->constrained($familiesTable)->restrictOnDelete();
            $table->string('kind', 30);
            $table->decimal('amount', 14, 2);
            $table->text('reason');
            $table->foreignId('authorized_by')->constrained($usersTable)->restrictOnDelete();
            $table->foreignId('created_by')->constrained($usersTable)->restrictOnDelete();
            $table->foreignId('reversed_adjustment_id')->nullable()->constrained('financial_adjustments')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE financial_adjustments ADD CONSTRAINT adjustments_kind_check CHECK (kind IN ('discount', 'scholarship', 'correction', 'waiver', 'surcharge'))");
            DB::statement('ALTER TABLE financial_adjustments ADD CONSTRAINT adjustments_amount_check CHECK (amount <> 0)');
            DB::statement('ALTER TABLE financial_adjustments ADD CONSTRAINT adjustments_reason_check CHECK (length(reason) >= 10)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_adjustments');
    }
};
