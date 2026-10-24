<?php

declare(strict_types=1);

namespace Cai\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class DbReset extends Command
{
    protected $signature = 'cai:db:reset {--force : Forzar sin confirmación}';

    protected $description = 'Recrea schemas y migraciones de módulos (solo desarrollo/testing)';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Esto borrará datos. ¿Continuar?')) {
            return self::FAILURE;
        }

        $schemas = config('cai.schemas', [
            'identity', 'students', 'scheduling', 'care', 'billing', 'documents', 'notifications', 'reporting',
        ]);

        foreach ($schemas as $schema) {
            $this->components->task("Recreando schema {$schema}", function () use ($schema): bool {
                DB::statement("DROP SCHEMA IF EXISTS {$schema} CASCADE");
                DB::statement("CREATE SCHEMA {$schema}");

                return true;
            });
        }

        $this->call('cai:migrate');

        return self::SUCCESS;
    }
}
