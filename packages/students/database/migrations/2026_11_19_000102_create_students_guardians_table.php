<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * students.guardians — 03.02 §3. Un tutor puede pertenecer a varias
 * familias: la relación se hace vía student_guardians, no con un FK directo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('document_id', 30)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 254)->nullable();
            $table->text('address')->nullable();
            $table->string('occupation', 120)->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['document_id']);
            $table->index(['last_name']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE guardians ADD CONSTRAINT guardians_status_check CHECK (status IN ('active', 'inactive'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
