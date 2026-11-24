<?php

declare(strict_types=1);

namespace Cai\Billing\Support;

use InvalidArgumentException;

/**
 * Importes en guaraníes como cadenas decimales (NUMERIC 14,2) + bcmath.
 * Nunca float: 0.1 + 0.2 debe dar 0.30, no 0.30000000000000004.
 *
 * 08.08 §2.2 / P2: el redondeo se aplica SOLO en `format()`, nunca al guardar.
 * El modo se lee de `cai.finanzas.rounding_mode` (none|100|1000).
 */
final readonly class Money
{
    private function __construct(public string $value) {}

    public static function parse(string|int $amount): self
    {
        $raw = (string) $amount;
        if (preg_match('/^-?\d+(\.\d{1,2})?$/', $raw) !== 1) {
            throw new InvalidArgumentException("Importe inválido: {$raw}. Use decimal con hasta 2 lugares.");
        }

        return new self(bcadd($raw, '0', 2));
    }

    public static function zero(): self
    {
        return new self('0.00');
    }

    public function plus(self $other): self
    {
        return new self(bcadd($this->value, $other->value, 2));
    }

    public function minus(self $other): self
    {
        return new self(bcsub($this->value, $other->value, 2));
    }

    public function isZero(): bool
    {
        return bccomp($this->value, '0', 2) === 0;
    }

    public function isNegative(): bool
    {
        return bccomp($this->value, '0', 2) < 0;
    }

    public function isGreaterThan(self $other): bool
    {
        return bccomp($this->value, $other->value, 2) > 0;
    }

    public function isLessThan(self $other): bool
    {
        return bccomp($this->value, $other->value, 2) < 0;
    }

    /** Presentación centralizada: mismo formato en todo el sistema (E-11). */
    public function format(): string
    {
        $value = $this->value;
        $mode = (string) config('cai.finanzas.rounding_mode', 'none');

        if ($mode === '100' || $mode === '1000') {
            $unit = $mode === '100' ? '100' : '1000';
            $negative = bccomp($value, '0', 2) < 0;
            $absolute = ltrim($value, '-');
            $half = bcdiv($unit, '2', 2);
            $blocks = bcdiv(bcadd($absolute, $half, 2), $unit, 0);
            $rounded = bcmul($blocks, $unit, 2);
            $value = $negative ? '-'.$rounded : $rounded;
        }

        $negative = str_starts_with($value, '-');
        $absolute = ltrim($value, '-');
        [$integer, $decimal] = array_pad(explode('.', $absolute), 2, '00');
        $grouped = (string) preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $integer);

        return ($negative ? '-' : '').$grouped.','.$decimal.' Gs.';
    }
}
