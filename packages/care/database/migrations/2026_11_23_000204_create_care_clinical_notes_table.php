<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * care.clinical_notes — F5 E-01/E-02 (03.05 §5, ADR-0006).
 * `content` guarda el ciphertext AES-256-GCM generado por ClinicalEncrypted.
 */
return new class extends Migration
{
    public function up(): void
    {
        $usersTable = DB::getDriverName() === 'pgsql' ? 'identity.users' : 'users';
        $studentsTable = DB::getDriverName() === 'pgsql' ? 'students.students' : 'students';
        $sessionsTable = DB::getDriverName() === 'pgsql' ? 'scheduling.sessions' : 'scheduling_sessions';

        Schema::create('clinical_notes', function (Blueprint $table) use ($usersTable, $studentsTable, $sessionsTable): void {
            $table->id();
            $table->foreignId('student_id')->constrained($studentsTable)->restrictOnDelete();
            $table->foreignId('author_user_id')->constrained($usersTable)->restrictOnDelete();
            $table->foreignId('session_id')->nullable()->constrained($sessionsTable)->nullOnDelete();
            $table->foreignId('care_plan_id')->nullable()->constrained('care_plans')->nullOnDelete();
            $table->string('note_type', 40);
            $table->string('status', 20)->default('draft');
            $table->text('content');
            $table->string('summary', 300)->nullable();
            $table->foreignId('supersedes_note_id')->nullable()->constrained('clinical_notes')->nullOnDelete();
            $table->text('correction_reason')->nullable();
            $table->dateTimeTz('recorded_at')->useCurrent();
            $table->dateTimeTz('finalized_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'recorded_at']);
            $table->index(['author_user_id', 'recorded_at']);
            $table->index(['session_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE clinical_notes ADD CONSTRAINT clinical_notes_status_check CHECK (status IN ('draft', 'finalized', 'superseded'))");
            DB::statement("ALTER TABLE clinical_notes ADD CONSTRAINT clinical_notes_type_check CHECK (note_type IN ('session', 'evaluation', 'progress', 'incident', 'family_contact', 'referral', 'administrative'))");
            DB::statement("ALTER TABLE clinical_notes ADD CONSTRAINT clinical_notes_finalized_check CHECK (status = 'draft' OR finalized_at IS NOT NULL)");
            DB::statement('ALTER TABLE clinical_notes ADD CONSTRAINT clinical_notes_correction_check CHECK (supersedes_note_id IS NULL OR correction_reason IS NOT NULL)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_notes');
    }
};
