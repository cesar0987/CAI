<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * scheduling.session_professionals — F4 E-01 (03.04 §4-§5).
 *
 * `starts_at`/`ends_at` están denormalizados aquí para poder aplicar la
 * restricción EXCLUDE de no-superposición en PostgreSQL (G4). Los triggers
 * `sync_session_times` y `update_session_times` se crean con el motor real;
 * en SQLite la garantía se refuerza en la Action de dominio.
 */
return new class extends Migration
{
    public function up(): void
    {
        $sessionsTable = DB::getDriverName() === 'pgsql' ? 'sessions' : 'scheduling_sessions';

        Schema::create('session_professionals', function (Blueprint $table) use ($sessionsTable): void {
            $table->foreignId('session_id')->constrained($sessionsTable)->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained('professionals')->cascadeOnDelete();
            $table->string('assignment_role', 50)->default('responsible');
            $table->dateTimeTz('starts_at');
            $table->dateTimeTz('ends_at');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['session_id', 'professional_id']);
            $table->index(['professional_id', 'starts_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE session_professionals ADD CONSTRAINT session_professionals_role_check CHECK (assignment_role IN ('responsible', 'assistant', 'observer'))");
            DB::statement('ALTER TABLE session_professionals ADD CONSTRAINT session_professionals_time_check CHECK (ends_at > starts_at)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('session_professionals');
    }
};
