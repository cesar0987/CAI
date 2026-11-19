<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * identity.roles — 03.01 §3. Compatible con spatie/laravel-permission
 * (guard_name + unique compuesto); label/description son las columnas de
 * dominio del diseño CAI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 60);
            $table->string('guard_name', 125)->default('web');
            $table->string('label', 120);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
