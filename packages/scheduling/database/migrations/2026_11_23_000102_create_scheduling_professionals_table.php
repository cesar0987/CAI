<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * scheduling.professionals — F4 E-01 (03.03 §3).
 * `user_id` opcional y único: no todo profesional es usuario del sistema.
 */
return new class extends Migration
{
    public function up(): void
    {
        $usersTable = DB::getDriverName() === 'pgsql' ? 'identity.users' : 'users';

        Schema::create('professionals', function (Blueprint $table) use ($usersTable): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained($usersTable)->nullOnDelete();
            $table->string('display_name', 200);
            $table->string('specialty', 120)->nullable();
            $table->string('document_id', 30)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 254)->nullable();
            $table->string('license_number', 80)->nullable();
            $table->string('status', 20)->default('active');
            $table->date('hired_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE professionals ADD CONSTRAINT professionals_status_check CHECK (status IN ('active', 'inactive', 'on_leave'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};
