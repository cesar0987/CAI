<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * scheduling.sessions — F4 E-01 (03.04 §3).
 * `starts_at`/`ends_at` con zona horaria (TIMESTAMPTZ en PostgreSQL).
 * La reprogramación crea una sesión nueva enlazada por `rescheduled_from_id`.
 */
return new class extends Migration
{
    public function up(): void
    {
        $usersTable = DB::getDriverName() === 'pgsql' ? 'identity.users' : 'users';
        // En SQLite la tabla base `sessions` del driver de sesiones ya existe;
        // el schema `scheduling` de PostgreSQL no tiene ese conflicto.
        $sessionsTable = DB::getDriverName() === 'pgsql' ? 'sessions' : 'scheduling_sessions';

        Schema::create($sessionsTable, function (Blueprint $table) use ($usersTable, $sessionsTable): void {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->string('session_type', 20);
            $table->dateTimeTz('starts_at');
            $table->dateTimeTz('ends_at');
            $table->string('status', 20)->default('scheduled');
            $table->foreignId('rescheduled_from_id')->nullable()->constrained($sessionsTable)->nullOnDelete();
            $table->string('location', 150)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained($usersTable)->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained($usersTable)->nullOnDelete();
            $table->timestamps();

            $table->index(['starts_at']);
            $table->index(['service_id', 'starts_at']);
            $table->index(['status', 'starts_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE sessions ADD CONSTRAINT sessions_type_check CHECK (session_type IN ('individual', 'group'))");
            DB::statement("ALTER TABLE sessions ADD CONSTRAINT sessions_status_check CHECK (status IN ('scheduled', 'completed', 'cancelled', 'rescheduled'))");
            DB::statement('ALTER TABLE sessions ADD CONSTRAINT sessions_time_check CHECK (ends_at > starts_at)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
