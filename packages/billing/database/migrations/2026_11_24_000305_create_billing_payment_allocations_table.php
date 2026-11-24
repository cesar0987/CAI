<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** billing.payment_allocations — 03.06 §6: el puente entre pago y cargo. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('charge_id')->constrained('charges')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['payment_id', 'charge_id']);
            $table->index(['payment_id']);
            $table->index(['charge_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE payment_allocations ADD CONSTRAINT allocations_amount_check CHECK (amount > 0)');
            // Los topes por pago y por cargo se imponen con triggers en 000309.
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
