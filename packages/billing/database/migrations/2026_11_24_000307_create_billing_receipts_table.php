<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** billing.receipts — 03.06 §7: un recibo por pago, nunca por cargo. */
return new class extends Migration
{
    public function up(): void
    {
        $usersTable = DB::getDriverName() === 'pgsql' ? 'identity.users' : 'users';

        Schema::create('receipts', function (Blueprint $table) use ($usersTable): void {
            $table->id();
            $table->foreignId('payment_id')->unique()->constrained('payments')->restrictOnDelete();
            $table->string('receipt_number', 50)->unique();
            $table->dateTimeTz('issued_at')->useCurrent();
            $table->string('status', 20)->default('issued');
            $table->text('void_reason')->nullable();
            $table->dateTimeTz('voided_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained($usersTable)->nullOnDelete();
            $table->decimal('total', 14, 2);
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE receipts ADD CONSTRAINT receipts_status_check CHECK (status IN ('issued', 'voided'))");
            DB::statement("ALTER TABLE receipts ADD CONSTRAINT receipts_void_check CHECK (status <> 'voided' OR void_reason IS NOT NULL)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
