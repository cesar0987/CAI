<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * students.family_account_members — 03.02 §6. Historial de pertenencia:
 * las filas se cierran con valid_until, nunca se borran (I-04).
 *
 * En PostgreSQL se añade la restricción de exclusión por rango (una cuenta
 * activa por alumno). En SQLite no existe EXCLUDE: la invariante se aplica
 * en TransferFamilyMembership dentro de una transacción y se cubre con tests.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_account_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('family_account_id')->constrained('family_accounts')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->string('role', 20)->default('dependiente');
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->index(['family_account_id', 'valid_until']);
            $table->index(['student_id', 'valid_from']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE family_account_members ADD CONSTRAINT family_account_members_role_check CHECK (role IN ('titular', 'dependiente'))");
            DB::statement('ALTER TABLE family_account_members ADD CONSTRAINT family_account_members_valid_check CHECK (valid_until IS NULL OR valid_until >= valid_from)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('family_account_members');
    }
};
