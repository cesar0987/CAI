<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * scheduling.attendance_records — F4 E-01 (03.04 §7).
 * Un registro por participante: una corrección actualiza la fila y deja el
 * diff en audit_events (`attendance.corrected`) en pasos posteriores de F4.
 */
return new class extends Migration
{
    public function up(): void
    {
        $usersTable = DB::getDriverName() === 'pgsql' ? 'identity.users' : 'users';

        Schema::create('attendance_records', function (Blueprint $table) use ($usersTable): void {
            $table->id();
            $table->foreignId('participant_id')->unique()->constrained('session_participants')->cascadeOnDelete();
            $table->string('status', 20);
            $table->foreignId('recorded_by')->constrained($usersTable)->restrictOnDelete();
            $table->dateTimeTz('recorded_at')->useCurrent();
            $table->text('notes')->nullable();

            $table->index(['status']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE attendance_records ADD CONSTRAINT attendance_status_check CHECK (status IN ('present', 'absent', 'late', 'excused'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
