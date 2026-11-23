<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * care.clinical_note_access_grants — 03.05 §6 (E-05/E-06 de F5).
 */
return new class extends Migration
{
    public function up(): void
    {
        $usersTable = DB::getDriverName() === 'pgsql' ? 'identity.users' : 'users';
        $studentsTable = DB::getDriverName() === 'pgsql' ? 'students.students' : 'students';

        Schema::create('clinical_note_access_grants', function (Blueprint $table) use ($usersTable, $studentsTable): void {
            $table->id();
            $table->foreignId('clinical_note_id')->nullable()->constrained('clinical_notes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained($studentsTable)->cascadeOnDelete();
            $table->foreignId('granted_to_user_id')->constrained($usersTable)->cascadeOnDelete();
            $table->foreignId('granted_by_user_id')->constrained($usersTable)->restrictOnDelete();
            $table->text('reason');
            $table->dateTimeTz('expires_at');
            $table->dateTimeTz('revoked_at')->nullable();
            $table->foreignId('revoked_by_user_id')->nullable()->constrained($usersTable)->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['granted_to_user_id', 'expires_at']);
            $table->index(['student_id', 'expires_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE clinical_note_access_grants ADD CONSTRAINT grants_reason_check CHECK (length(reason) >= 20)');
            DB::statement('ALTER TABLE clinical_note_access_grants ADD CONSTRAINT grants_expiry_check CHECK (expires_at > created_at)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_note_access_grants');
    }
};
