<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * identity.permissions — 03.01 §4. Formato `<módulo>.<recurso>.<acción>`.
 * `is_sensitive` marca permisos que tocan datos clínicos o financieros.
 * Compatible con spatie/laravel-permission (guard_name + unique compuesto).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('guard_name', 125)->default('web');
            $table->string('label', 180);
            $table->string('module', 40);
            $table->boolean('is_sensitive')->default(false);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
