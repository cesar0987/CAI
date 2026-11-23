<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * scheduling.services — F4 E-01 (03.03 §2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150)->unique();
            $table->text('description')->nullable();
            $table->integer('default_duration_minutes')->nullable();
            $table->string('modality', 20)->default('both');
            $table->string('color', 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE services ADD CONSTRAINT services_modality_check CHECK (modality IN ('individual', 'group', 'both'))");
            DB::statement('ALTER TABLE services ADD CONSTRAINT services_duration_check CHECK (default_duration_minutes IS NULL OR default_duration_minutes > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
