<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * students.student_guardians — 03.02 §4. Pivote con atributos de la relación
 * y un único tutor principal vigente por alumno (índice parcial).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_guardians', function (Blueprint $table): void {
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained('guardians')->restrictOnDelete();
            $table->string('relationship', 50);
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_pick_up')->default(false);
            $table->boolean('receives_admin_messages')->default(false);
            $table->boolean('is_emergency_contact')->default(false);
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['student_id', 'guardian_id']);
            $table->index(['guardian_id']);
        });

        // Índice parcial: SQL válido en SQLite y PostgreSQL.
        DB::statement('CREATE UNIQUE INDEX student_guardians_one_primary ON student_guardians (student_id) WHERE is_primary = 1 AND valid_until IS NULL');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE student_guardians ADD CONSTRAINT student_guardians_valid_check CHECK (valid_until IS NULL OR valid_until >= valid_from)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guardians');
    }
};
