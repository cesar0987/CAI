<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * care.care_goals — 03.05 §4.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_goals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('care_plan_id')->constrained('care_plans')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->text('baseline')->nullable();
            $table->string('status', 20)->default('pending');
            $table->date('target_date')->nullable();
            $table->date('review_date')->nullable();
            $table->text('progress_notes')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['care_plan_id', 'status']);
            $table->index(['review_date']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE care_goals ADD CONSTRAINT care_goals_status_check CHECK (status IN ('pending', 'in_progress', 'achieved', 'dropped', 'postponed'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('care_goals');
    }
};
