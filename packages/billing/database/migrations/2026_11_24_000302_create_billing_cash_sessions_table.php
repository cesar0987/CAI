<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** billing.cash_sessions — 03.06 §8. Se crea antes que payments por su FK. */
return new class extends Migration
{
    public function up(): void
    {
        $usersTable = DB::getDriverName() === 'pgsql' ? 'identity.users' : 'users';

        Schema::create('cash_sessions', function (Blueprint $table) use ($usersTable): void {
            $table->id();
            $table->foreignId('user_id')->constrained($usersTable)->restrictOnDelete();
            $table->dateTimeTz('opened_at')->useCurrent();
            $table->dateTimeTz('closed_at')->nullable();
            $table->decimal('opening_amount', 14, 2)->default(0);
            $table->decimal('closing_amount', 14, 2)->nullable();
            $table->decimal('expected_amount', 14, 2)->nullable();
            $table->decimal('difference', 14, 2)->nullable();
            $table->string('status', 20)->default('open');
            $table->text('notes')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained($usersTable)->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE cash_sessions ADD CONSTRAINT cash_sessions_status_check CHECK (status IN ('open', 'closed'))");
            DB::statement('ALTER TABLE cash_sessions ADD CONSTRAINT cash_sessions_close_check CHECK (status <> \'closed\' OR closed_at IS NOT NULL)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};
