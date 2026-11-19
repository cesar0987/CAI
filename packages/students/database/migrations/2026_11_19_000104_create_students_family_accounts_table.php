<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * students.family_accounts — 03.02 §5. `credit_balance` NO es columna:
 * se calcula en F6 desde cargos e imputaciones (ADR-0007).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('account_code', 30)->unique();
            $table->string('display_name', 180);
            $table->string('status', 20)->default('active');
            $table->char('currency', 3)->default('PYG');
            $table->foreignId('responsible_guardian_id')->nullable()->constrained('guardians')->restrictOnDelete();
            $table->text('address')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 254)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE family_accounts ADD CONSTRAINT family_accounts_status_check CHECK (status IN ('active', 'inactive', 'closed'))");
            DB::statement("ALTER TABLE family_accounts ADD CONSTRAINT family_accounts_currency_check CHECK (currency = 'PYG')");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('family_accounts');
    }
};
