<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * scheduling.group_memberships — F4 E-01 (03.03 §6).
 * Historial: una fila se cierra con valid_until, nunca se borra.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->unique(['group_id', 'student_id', 'valid_from']);
            $table->index(['group_id', 'valid_until']);
            $table->index(['student_id', 'valid_until']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE group_memberships ADD CONSTRAINT group_memberships_valid_check CHECK (valid_until IS NULL OR valid_until >= valid_from)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('group_memberships');
    }
};
