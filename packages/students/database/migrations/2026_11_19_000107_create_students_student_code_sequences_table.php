<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * students.student_code_sequences — tabla de apoyo necesaria para el
 * generador de 08.05 §2.2 (ALU-AAAA-NNNN). No aparece en la lista de seis
 * tablas de 08.05 §2.1, pero el generador la requiere: se agrega como
 * séptima migración de soporte, documentada en el REG de F3.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_code_sequences', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('year')->unique();
            $table->unsignedInteger('current')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_code_sequences');
    }
};
