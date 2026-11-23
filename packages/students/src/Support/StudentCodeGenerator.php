<?php

declare(strict_types=1);

namespace Cai\Students\Support;

use Cai\Students\Models\StudentCodeSequence;
use Illuminate\Support\Facades\DB;

/**
 * Código legible y estable: ALU-AAAA-NNNN-C (08.05 §2.2, ADR-0020).
 *
 * `C` es un dígito Luhn calculado sobre los ocho dígitos `AAAANNNN`, para que
 * también se detecte un error al transcribir el año. La secuencia por año se
 * bloquea con `lockForUpdate` para no duplicar códigos bajo concurrencia.
 */
final class StudentCodeGenerator
{
    public const CODE_PATTERN = '/^ALU-(\d{4})-(\d{4})-(\d)$/';

    public function generate(int $year, ?string $preferred = null): string
    {
        if ($preferred !== null) {
            $preferred = trim($preferred);

            if (! $this->isValid($preferred)) {
                throw new \InvalidArgumentException("El código preferido {$preferred} no tiene un checksum válido.");
            }

            return $preferred;
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
            $payload = sprintf('%04d%04d', $year, $sequence->current);

            return sprintf('ALU-%04d-%04d-%s', $year, $sequence->current, $this->checkDigit($payload));
        });
    }

    public function isValid(string $code): bool
    {
        $code = trim($code);

        if (preg_match(self::CODE_PATTERN, $code, $matches) !== 1) {
            return false;
        }

        return $this->checkDigit($matches[1].$matches[2]) === $matches[3];
    }

    /**
     * Dígito de control Luhn (módulo 10) sobre los ocho dígitos AAAANNNN.
     */
    public function checkDigit(string $payload): string
    {
        if (preg_match('/^\d{8}$/', $payload) !== 1) {
            throw new \InvalidArgumentException('El payload del checksum debe tener 8 dígitos.');
        }

        $sum = 0;
        $double = true;

        for ($i = strlen($payload) - 1; $i >= 0; $i--) {
            $digit = (int) $payload[$i];

            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = ! $double;
        }

        return (string) ((10 - ($sum % 10)) % 10);
    }
}
