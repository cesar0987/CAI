<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * identity.feature_flags — kill switches por módulo/funcionalidad (03.01 §6,
 * 06.04). El cambio de un flag se audita.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table): void {
            $table->string('key', 80)->primary();
            $table->string('description', 255);
            $table->boolean('is_enabled')->default(false);
            $table->string('scope', 30)->default('module');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE feature_flags ADD CONSTRAINT feature_flags_scope_check CHECK (scope IN ('module', 'feature', 'experiment'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};
