<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * billing.charges — 03.06 §4. Conserva cuenta, alumno e inscripción (ADR-0007 §2.2).
 * No tiene columna de saldo: se calcula desde payment_allocations.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        $familiesTable = $driver === 'pgsql' ? 'students.family_accounts' : 'family_accounts';
        $studentsTable = $driver === 'pgsql' ? 'students.students' : 'students';
        $enrollmentsTable = $driver === 'pgsql' ? 'scheduling.enrollments' : 'enrollments';
        $usersTable = $driver === 'pgsql' ? 'identity.users' : 'users';

        Schema::create('charges', function (Blueprint $table) use ($familiesTable, $studentsTable, $enrollmentsTable, $usersTable): void {
            $table->id();
            $table->foreignId('family_account_id')->constrained($familiesTable)->restrictOnDelete();
            $table->foreignId('student_id')->constrained($studentsTable)->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained($enrollmentsTable)->nullOnDelete();
            $table->foreignId('fee_plan_id')->nullable()->constrained('fee_plans')->nullOnDelete();
            $table->string('description', 200);
            $table->date('period')->nullable();
            $table->date('due_date');
            $table->decimal('amount', 14, 2);
            $table->decimal('amount_paid', 14, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->foreignId('generated_by')->nullable()->constrained($usersTable)->nullOnDelete();
            $table->string('generation_source', 20)->default('plan');
            $table->timestamps();

            $table->index(['family_account_id', 'due_date']);
            $table->index(['student_id', 'period']);
            $table->index(['status', 'due_date']);
            $table->index(['period']);
        });

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE charges ADD CONSTRAINT charges_amount_check CHECK (amount > 0)');
            DB::statement('ALTER TABLE charges ADD CONSTRAINT charges_paid_check CHECK (amount_paid >= 0 AND amount_paid <= amount)');
            DB::statement("ALTER TABLE charges ADD CONSTRAINT charges_status_check CHECK (status IN ('pending', 'partial', 'paid', 'voided'))");
            DB::statement("ALTER TABLE charges ADD CONSTRAINT charges_source_check CHECK (generation_source IN ('plan', 'manual', 'session'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('charges');
    }
};
