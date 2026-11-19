<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * identity.users — 03.01 §2 + 08.04 §2.1.
 *
 * El email es el identificador de login. `status` reemplaza al borrado físico
 * (nunca se elimina un usuario). `professional_id` queda sin FK hasta F4:
 * la tabla scheduling.professionals todavía no existe (expand-contract).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 254)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->string('document_id', 60)->nullable()->unique();
            $table->unsignedBigInteger('professional_id')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamp('last_login_at')->nullable();
            $table->smallInteger('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->string('mfa_secret', 255)->nullable();
            $table->timestamp('mfa_enabled_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('active', 'inactive', 'suspended'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
