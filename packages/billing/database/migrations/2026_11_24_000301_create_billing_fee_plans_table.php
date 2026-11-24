<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * billing.fee_plans — 03.06 §3 (equivale a `billing_plans` en 08.08 §3.1).
 * `periodicity = session` cubre P1 sin decidir el modelo de cobro.
 */
return new class extends Migration
{
    public function up(): void
    {
        $servicesTable = DB::getDriverName() === 'pgsql' ? 'scheduling.services' : 'services';

        Schema::create('fee_plans', function (Blueprint $table) use ($servicesTable): void {
            $table->id();
            $table->foreignId('service_id')->constrained($servicesTable)->restrictOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('periodicity', 20)->default('monthly');
            $table->integer('sessions_included')->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->index(['service_id', 'is_active']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE fee_plans ADD CONSTRAINT fee_plans_amount_check CHECK (amount > 0)');
            DB::statement('ALTER TABLE fee_plans ADD CONSTRAINT fee_plans_discount_check CHECK (discount_percent >= 0 AND discount_percent <= 100)');
            DB::statement("ALTER TABLE fee_plans ADD CONSTRAINT fee_plans_period_check CHECK (periodicity IN ('monthly', 'bimonthly', 'weekly', 'annual', 'session'))");
            DB::statement('ALTER TABLE fee_plans ADD CONSTRAINT fee_plans_dates_check CHECK (valid_until IS NULL OR valid_until >= valid_from)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_plans');
    }
};
