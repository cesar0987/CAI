<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * scheduling.groups — F4 E-01 (03.03 §5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->string('name', 150);
            $table->foreignId('responsible_professional_id')->nullable()->constrained('professionals')->restrictOnDelete();
            $table->string('room', 80)->nullable();
            $table->smallInteger('capacity');
            $table->integer('max_duration_minutes')->default(60);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['service_id', 'name']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE groups ADD CONSTRAINT groups_capacity_check CHECK (capacity > 0)');
            DB::statement('ALTER TABLE groups ADD CONSTRAINT groups_duration_check CHECK (max_duration_minutes > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
