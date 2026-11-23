<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * scheduling.session_participants — F4 E-01 (03.04 §6).
 * Un alumno participa como máximo una vez por sesión.
 */
return new class extends Migration
{
    public function up(): void
    {
        $usersTable = DB::getDriverName() === 'pgsql' ? 'identity.users' : 'users';
        $sessionsTable = DB::getDriverName() === 'pgsql' ? 'sessions' : 'scheduling_sessions';

        Schema::create('session_participants', function (Blueprint $table) use ($usersTable, $sessionsTable): void {
            $table->id();
            $table->foreignId('session_id')->constrained($sessionsTable)->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->foreignId('added_by')->nullable()->constrained($usersTable)->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['session_id', 'student_id']);
            $table->index(['student_id', 'session_id']);
            $table->index(['session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_participants');
    }
};
