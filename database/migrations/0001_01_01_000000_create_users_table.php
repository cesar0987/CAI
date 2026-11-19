<?php

use Illuminate\Database\Migrations\Migration;

/**
 * ANDAMIAJE RETIRADO (F2, 2026-11-19).
 *
 * Las tablas users, password_reset_tokens y sessions del scaffolding inicial
 * de Laravel pasaron a ser propiedad del módulo identity (03.01, 08.04 §2.1):
 * las crean las migraciones de packages/identity/database/migrations.
 *
 * Este archivo se conserva como no-op para no romper el historial de
 * migraciones ya ejecutado en entornos de desarrollo. No crea nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        // identity es dueño de users, password_reset_tokens y sessions.
    }

    public function down(): void
    {
        // Sin efecto: el down real vive en las migraciones de identity.
    }
};
