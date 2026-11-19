<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * students.students — F3 E-01 (03.02 §2).
 *
 * `student_code` estable y nunca reutilizado. `document_id` es texto (Paraguay
 * no tiene documento numérico único para menores). Soft delete: RN-007.
 * Los FK a identity.users se cualifican en PostgreSQL (otro schema).
 */
return new class extends Migration
{
    public function up(): void
    {
        $usersTable = DB::getDriverName() === 'pgsql' ? 'identity.users' : 'users';

        Schema::create('students', function (Blueprint $table) use ($usersTable): void {
            $table->id();
            $table->string('student_code', 30)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('status', 20)->default('active');
            $table->string('document_id', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('district', 80)->nullable();
            $table->string('city', 80)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 254)->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->date('admission_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained($usersTable)->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained($usersTable)->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['last_name', 'first_name']);
            $table->index(['document_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE students ADD CONSTRAINT students_status_check CHECK (status IN ('active', 'inactive', 'withdrawn'))");
            DB::statement('ALTER TABLE students ADD CONSTRAINT students_birth_date_check CHECK (birth_date IS NULL OR birth_date <= CURRENT_DATE)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
