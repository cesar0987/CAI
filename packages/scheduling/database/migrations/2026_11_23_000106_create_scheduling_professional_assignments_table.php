<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * scheduling.professional_assignments — F4 E-01 (03.03 §7).
 * Base del alcance clínico: `care` consultará este contrato en F5.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professional_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('professional_id')->constrained('professionals')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('role', 40)->default('attendant');
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->index(['professional_id', 'valid_until']);
            $table->index(['student_id', 'valid_until']);
        });

        // S-06: un solo responsable vigente por alumno (válido en ambos drivers).
        DB::statement('CREATE UNIQUE INDEX professional_assignments_one_responsible ON professional_assignments (student_id) WHERE role = \'responsible\' AND valid_until IS NULL');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE professional_assignments ADD CONSTRAINT professional_assignments_role_check CHECK (role IN ('attendant', 'responsible', 'supervisor', 'referent'))");
            DB::statement('ALTER TABLE professional_assignments ADD CONSTRAINT professional_assignments_valid_check CHECK (valid_until IS NULL OR valid_until >= valid_from)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_assignments');
    }
};
