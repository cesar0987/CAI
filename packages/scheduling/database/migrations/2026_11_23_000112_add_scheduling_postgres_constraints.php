<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Garantías de base de datos que solo existen en PostgreSQL (03.04 §4, G4).
 * En SQLite se omiten: la Action `ScheduleSession` hace el chequeo equivalente.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
        DB::statement("ALTER TABLE session_professionals ADD CONSTRAINT no_overlapping_sessions EXCLUDE USING gist (professional_id WITH =, tstzrange(starts_at, ends_at, '[]') WITH &&)");

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION scheduling.sync_session_times()
            RETURNS TRIGGER AS $$
            BEGIN
                SELECT starts_at, ends_at INTO NEW.starts_at, NEW.ends_at
                FROM scheduling.sessions WHERE id = NEW.session_id;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql
            SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER session_professionals_sync
                BEFORE INSERT OR UPDATE ON scheduling.session_professionals
                FOR EACH ROW EXECUTE FUNCTION scheduling.sync_session_times()
            SQL);

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION scheduling.update_session_times()
            RETURNS TRIGGER AS $$
            BEGIN
                UPDATE scheduling.session_professionals
                SET starts_at = NEW.starts_at, ends_at = NEW.ends_at
                WHERE session_id = NEW.id;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql
            SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER sessions_update_times
                AFTER UPDATE OF starts_at, ends_at ON scheduling.sessions
                FOR EACH ROW EXECUTE FUNCTION scheduling.update_session_times()
            SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP TRIGGER IF EXISTS sessions_update_times ON scheduling.sessions');
        DB::statement('DROP TRIGGER IF EXISTS session_professionals_sync ON scheduling.session_professionals');
        DB::statement('DROP FUNCTION IF EXISTS scheduling.update_session_times()');
        DB::statement('DROP FUNCTION IF EXISTS scheduling.sync_session_times()');
        DB::statement('ALTER TABLE session_professionals DROP CONSTRAINT IF EXISTS no_overlapping_sessions');
    }
};
