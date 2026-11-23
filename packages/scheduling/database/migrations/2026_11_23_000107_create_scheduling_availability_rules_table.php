<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * scheduling.availability_rules — F4 E-01 (03.03 §8).
 * `weekday`: 0 = domingo … 6 = sábado (convención PostgreSQL).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availability_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('professional_id')->constrained('professionals')->cascadeOnDelete();
            $table->smallInteger('weekday');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room', 80)->nullable();
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->index(['professional_id', 'weekday']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE availability_rules ADD CONSTRAINT availability_weekday_check CHECK (weekday BETWEEN 0 AND 6)');
            DB::statement('ALTER TABLE availability_rules ADD CONSTRAINT availability_time_check CHECK (end_time > start_time)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_rules');
    }
};
