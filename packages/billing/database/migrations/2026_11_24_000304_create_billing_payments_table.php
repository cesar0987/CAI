<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** billing.payments — 03.06 §5. Un pago pertenece a la familia, no a un alumno. */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        $familiesTable = $driver === 'pgsql' ? 'students.family_accounts' : 'family_accounts';
        $usersTable = $driver === 'pgsql' ? 'identity.users' : 'users';

        Schema::create('payments', function (Blueprint $table) use ($familiesTable, $usersTable): void {
            $table->id();
            $table->foreignId('family_account_id')->constrained($familiesTable)->restrictOnDelete();
            $table->foreignId('cash_session_id')->nullable()->constrained('cash_sessions')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->dateTimeTz('paid_at');
            $table->string('method', 30);
            $table->string('reference', 100)->nullable();
            $table->string('payer_name', 150)->nullable();
            $table->string('status', 20)->default('confirmed');
            $table->foreignId('received_by')->constrained($usersTable)->restrictOnDelete();
            $table->text('void_reason')->nullable();
            $table->dateTimeTz('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained($usersTable)->nullOnDelete();
            $table->timestamps();

            $table->index(['family_account_id', 'paid_at']);
            $table->index(['status', 'paid_at']);
            $table->index(['received_by']);
        });

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_amount_check CHECK (amount > 0)');
            DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_method_check CHECK (method IN ('cash', 'bank_transfer', 'card', 'deposit', 'check'))");
            DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_status_check CHECK (status IN ('confirmed', 'voided'))");
            DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_void_check CHECK (status <> 'voided' OR void_reason IS NOT NULL)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
