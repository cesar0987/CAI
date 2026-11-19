<?php

declare(strict_types=1);

namespace Cai\Students\Support;

use Cai\Students\Models\StudentCodeSequence;
use Illuminate\Support\Facades\DB;

/**
 * Código legible y estable: ALU-AAAA-NNNN (08.05 §2.2).
 *
 * La secuencia por año se bloquea con `lockForUpdate` para no duplicar
 * códigos bajo concurrencia. El "checksum" que menciona el texto de 08.05
 * no aparece en el pseudocódigo: no se implementa hasta que Dirección
 * defina el algoritmo (no se inventa uno).
 */
final class StudentCodeGenerator
{
    public const PREFERRED_PATTERN = '/^ALU-\d{4}-\d{4}$/';

    public function generate(int $year, ?string $preferred = null): string
    {
        if ($preferred !== null && preg_match(self::PREFERRED_PATTERN, trim($preferred)) === 1) {
            return trim($preferred);
        }

        return DB::transaction(function () use ($year): string {
            $sequence = StudentCodeSequence::query()
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                $sequence = StudentCodeSequence::create(['year' => $year, 'current' => 0]);
            }

            $sequence->increment('current');

            return sprintf('ALU-%04d-%04d', $year, $sequence->current);
        });
    }

    public function isValid(string $code): bool
    {
        return preg_match(self::PREFERRED_PATTERN, trim($code)) === 1;
    }
}
