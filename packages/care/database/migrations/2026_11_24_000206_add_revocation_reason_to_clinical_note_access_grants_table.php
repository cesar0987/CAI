<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Motivo de revocación manual o por expiración automática (04.04 §3.3). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinical_note_access_grants', function (Blueprint $table): void {
            $table->text('revocation_reason')->nullable()->after('revoked_at');
        });
    }

    public function down(): void
    {
        Schema::table('clinical_note_access_grants', function (Blueprint $table): void {
            $table->dropColumn('revocation_reason');
        });
    }
};
