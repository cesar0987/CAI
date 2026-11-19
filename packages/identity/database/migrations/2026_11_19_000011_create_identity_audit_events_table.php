<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * identity.audit_events — append-only (03.01 §7, 04.05).
 *
 * Sin updated_at ni deleted_at: nadie modifica ni borra esta tabla.
 * `student_id` va desnormalizado y SIN FK a students (dependencia circular
 * entre schemas). `severity` la escribe el Auditor existente del paquete;
 * 03.01 no la lista para audit_events (sí para security_events): se incorpora
 * como columna additive para no romper el contrato ya implementado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_role', 60)->nullable();
            $table->string('action', 80);
            $table->string('entity_type', 80);
            $table->string('entity_id', 120)->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->json('changes')->nullable();
            $table->json('context')->nullable();
            $table->string('severity', 20)->default('info');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['actor_user_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['student_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
    }
};
