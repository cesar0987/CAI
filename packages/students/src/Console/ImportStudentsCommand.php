<?php

declare(strict_types=1);

namespace Cai\Students\Console;

use Cai\Students\Actions\ImportStudents;
use Cai\Students\Data\ImportStudentsData;
use Illuminate\Console\Command;

/**
 *   php artisan cai:import:students --file=/tmp/alumnos.csv --dry-run --report=/tmp/rechazos.csv
 *   php artisan cai:import:students --file=/tmp/alumnos.csv --execute
 *
 * El dry-run es el default: para escribir hay que pasar --execute
 * explícitamente. El CSV nunca entra al repositorio (08.05 §5).
 */
final class ImportStudentsCommand extends Command
{
    protected $signature = 'cai:import:students
        {--file= : Ruta al CSV de la planilla}
        {--dry-run : Validar sin escribir (default)}
        {--execute : Escribir en la base, tras validar el dry-run}
        {--report= : Ruta del CSV de rechazos}
        {--update : Reservado: actualizar registros existentes (aún no implementado)}';

    protected $description = 'Importa la planilla de alumnos con validación y reporte de rechazos';

    public function handle(ImportStudents $action): int
    {
        $file = (string) $this->option('file');
        if ($file === '' || ! is_readable($file)) {
            $this->error("Archivo no legible: {$file}");

            return self::FAILURE;
        }

        if ($this->option('update')) {
            $this->error('La opción --update no está implementada todavía: se rechaza por seguridad.');

            return self::FAILURE;
        }

        $dryRun = ! $this->option('execute');
        $report = $this->option('report');

        if ($report !== null && str_starts_with(realpath(dirname((string) $report)) ?: '', realpath(base_path()))) {
            $this->error('El reporte no puede escribirse dentro del repositorio: los CSV no se versionan.');

            return self::FAILURE;
        }

        $result = $action->execute(new ImportStudentsData(
            $file,
            $dryRun,
            $report !== null ? (string) $report : null,
        ));

        $this->info(sprintf(
            '%s: %d filas aceptadas, %d rechazadas.',
            $dryRun ? 'Simulación' : 'Importación',
            $result->accepted,
            count($result->rejected),
        ));

        if ($result->rejected !== []) {
            $this->table(['Fila', 'Código', 'Motivo'], array_map(
                fn (array $row): array => [$row['row'], $row['code'] ?? '', $row['reason']],
                $result->rejected,
            ));

            if ($report !== null) {
                $this->writeReport((string) $report, $result->rejected);
                $this->info("Reporte de rechazos: {$report}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<array{row: int, code: ?string, reason: string}>  $rejected
     */
    private function writeReport(string $path, array $rejected): void
    {
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new \RuntimeException("No se puede escribir el reporte: {$path}");
        }

        fputcsv($handle, ['row', 'code', 'reason']);
        foreach ($rejected as $row) {
            fputcsv($handle, [$row['row'], $row['code'] ?? '', $row['reason']]);
        }
        fclose($handle);
    }
}
