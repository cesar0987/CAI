<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * students.authorized_pickups — 03.02 §7. Terceros NO tutores autorizados
 * a retirar, con documento y vigencia. Una autorización vencida no habilita.
 */
return new class extends Migration
{
    public function up(): void
    {
        $usersTable = DB::getDriverName() === 'pgsql' ? 'identity.users' : 'users';

        Schema::create('authorized_pickups', function (Blueprint $table) use ($usersTable): void {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('full_name', 150);
            $table->string('document_id', 30);
            $table->string('phone', 40)->nullable();
            $table->string('relationship', 80)->nullable();
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained($usersTable)->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'is_active']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE authorized_pickups ADD CONSTRAINT authorized_pickups_valid_check CHECK (valid_until IS NULL OR valid_until >= valid_from)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('authorized_pickups');
    }
};
