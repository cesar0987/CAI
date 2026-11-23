<?php

declare(strict_types=1);

namespace Cai\Students\Import;

/**
 * Lector CSV con cabecera por nombre (08.05 §2.6). Nunca escribe nada.
 */
final class CsvStudentReader
{
    /**
     * @return array<int, array<string, string>>
     */
    public function read(string $filePath): array
    {
        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new \RuntimeException("No se puede leer el archivo: {$filePath}");
        }

        try {
            $header = fgetcsv($handle);
            if ($header === false) {
                return [];
            }

            $header = array_map(
                fn (string $column): string => trim(ltrim($column, "\xEF\xBB\xBF")),
                $header,
            );

            $rows = [];
            $rowNumber = 0;

            while (($values = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $row = [];
                foreach ($header as $index => $column) {
                    $row[$column] = trim((string) ($values[$index] ?? ''));
                }
                $rows[$rowNumber] = $row;
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }
}
