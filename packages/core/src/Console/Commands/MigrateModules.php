<?php

declare(strict_types=1);

namespace Cai\Core\Console\Commands;

use Illuminate\Console\Command;

final class MigrateModules extends Command
{
    protected $signature = 'cai:migrate {--schema=* : Solo estos schemas}';

    protected $description = 'Ejecuta migraciones por schema de módulos';

    public function handle(): int
    {
        $schemas = $this->option('schema') ?: config('cai.schemas', [
            'identity', 'students', 'scheduling', 'care', 'billing', 'documents', 'notifications', 'reporting',
        ]);

        foreach ($schemas as $schema) {
            $this->components->task("Migrando {$schema}", function () use ($schema): bool {
                $this->call('migrate', [
                    '--database' => $schema,
                    '--path' => base_path("packages/{$schema}/database/migrations"),
                    '--realpath' => false,
                    '--force' => true,
                ]);

                return true;
            });
        }

        return self::SUCCESS;
    }
}
