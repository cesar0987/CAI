<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * scheduling.enrollments — F4 E-01 (03.03 §4).
 * La exclusión de matrículas activas (S-01) es PostgreSQL-only y queda para
 * la verificación con el motor real; en SQLite se valida en la capa de dominio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['service_id', 'status']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE enrollments ADD CONSTRAINT enrollments_status_check CHECK (status IN ('active', 'paused', 'completed', 'cancelled'))");
            DB::statement('ALTER TABLE enrollments ADD CONSTRAINT enrollments_dates_check CHECK (end_date IS NULL OR end_date >= start_date)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
