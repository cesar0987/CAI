<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * care.care_plan_professionals — 03.05 §3. Un solo responsable por plan.
 */
return new class extends Migration
{
    public function up(): void
    {
        $professionalsTable = DB::getDriverName() === 'pgsql' ? 'scheduling.professionals' : 'professionals';

        Schema::create('care_plan_professionals', function (Blueprint $table) use ($professionalsTable): void {
            $table->foreignId('care_plan_id')->constrained('care_plans')->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained($professionalsTable)->restrictOnDelete();
            $table->string('role', 40)->default('responsible');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['care_plan_id', 'professional_id']);
        });

        DB::statement("CREATE UNIQUE INDEX care_plans_one_responsible ON care_plan_professionals (care_plan_id) WHERE role = 'responsible'");

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE care_plan_professionals ADD CONSTRAINT care_plan_professionals_role_check CHECK (role IN ('responsible', 'collaborator', 'reviewer'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('care_plan_professionals');
    }
};
