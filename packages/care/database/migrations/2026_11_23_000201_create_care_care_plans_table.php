<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * care.care_plans — F5 E-01 (03.03 §2 / 03.05 §2).
 * `diagnosis` queda en claro a la espera de P7 (ADR-0006 §2.1).
 */
return new class extends Migration
{
    public function up(): void
    {
        $usersTable = DB::getDriverName() === 'pgsql' ? 'identity.users' : 'users';
        $studentsTable = DB::getDriverName() === 'pgsql' ? 'students.students' : 'students';
        $professionalsTable = DB::getDriverName() === 'pgsql' ? 'scheduling.professionals' : 'professionals';

        Schema::create('care_plans', function (Blueprint $table) use ($usersTable, $studentsTable, $professionalsTable): void {
            $table->id();
            $table->foreignId('student_id')->constrained($studentsTable)->restrictOnDelete();
            $table->string('title', 200);
            $table->text('diagnosis')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('responsible_professional_id')->nullable()->constrained($professionalsTable)->restrictOnDelete();
            $table->text('goals_summary')->nullable();
            $table->smallInteger('review_interval_days')->default(90);
            $table->foreignId('created_by')->nullable()->constrained($usersTable)->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE care_plans ADD CONSTRAINT care_plans_status_check CHECK (status IN ('draft', 'active', 'under_review', 'closed'))");
            DB::statement('ALTER TABLE care_plans ADD CONSTRAINT care_plans_dates_check CHECK (end_date IS NULL OR end_date >= start_date)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('care_plans');
    }
};
