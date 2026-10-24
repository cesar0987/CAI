<?php

declare(strict_types=1);

namespace Cai\Core\Support;

use InvalidArgumentException;
use Stringable;

final class Money implements Stringable
{
    private const SCALE = 2;

    private function __construct(
        private readonly string $amount,
    ) {
    }

    public static function parse(string|int|float $amount): self
    {
        return new self(self::normalizeAmount((string) $amount));
    }

    public static function zero(): self
    {
        return new self('0.00');
    }

    public static function fromCents(int $cents): self
    {
        return new self(self::normalizeAmount(bcdiv((string) $cents, '100', self::SCALE)));
    }

    public static function format(self $money): string
    {
        return number_format((float) $money->amount, self::SCALE, ',', '.');
    }

    public function value(): string
    {
        return $this->amount;
    }

    public function __toString(): string
    {
        return $this->amount;
    }

    public function plus(self $other): self
    {
        return new self(bcadd($this->amount, $other->amount, self::SCALE));
    }

    public function add(self $other): self
    {
        return $this->plus($other);
    }

    public function minus(self $other): self
    {
        return new self(bcsub($this->amount, $other->amount, self::SCALE));
    }

    public function subtract(self $other): self
    {
        return $this->minus($other);
    }

    public function multiply(string|int|float $multiplier): self
    {
        return new self(bcmul($this->amount, self::normalizeAmount((string) $multiplier), self::SCALE));
    }

    public function divide(string|int|float $divisor): self
    {
        if (self::normalizeAmount((string) $divisor) === '0.00') {
            throw new InvalidArgumentException('No se puede dividir por cero.');
        }

        return new self(bcdiv($this->amount, self::normalizeAmount((string) $divisor), self::SCALE));
    }

    public function compare(self $other): int
    {
        return bccomp($this->amount, $other->amount, self::SCALE);
    }

    public function equals(self $other): bool
    {
        return $this->compare($other) === 0;
    }

    public function isZero(): bool
    {
        return $this->compare(self::zero()) === 0;
    }

    public function isPositive(): bool
    {
        return $this->compare(self::zero()) > 0;
    }

    public function isNegative(): bool
    {
        return $this->compare(self::zero()) < 0;
    }

    public function isLessThan(self $other): bool
    {
        return $this->compare($other) < 0;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->compare($other) > 0;
    }

    private static function normalizeAmount(string $amount): string
    {
        $amount = trim((string) $amount);

        if ($amount === '') {
            throw new InvalidArgumentException('El importe no puede estar vacío.');
        }

        if (! preg_match('/^[-+]?\d+(?:\.\d+)?$/', $amount)) {
            throw new InvalidArgumentException('El importe debe ser un número decimal válido.');
        }

        $sign = '';
        if (str_starts_with($amount, '+')) {
            $amount = substr($amount, 1);
        }

        if (str_starts_with($amount, '-')) {
            $sign = '-';
            $amount = substr($amount, 1);
        }

        if ($amount === '') {
            $amount = '0';
        }

        if (str_contains($amount, '.')) {
            [$intPart, $decimalPart] = explode('.', $amount, 2);
            $intPart = ltrim($intPart, '0');
            $decimalPart = rtrim($decimalPart, '0');

            if ($intPart === '') {
                $intPart = '0';
            }

            $amount = $intPart.'.'.$decimalPart;
        } else {
            $amount = ltrim($amount, '0');
            if ($amount === '') {
                $amount = '0';
            }
        }

        if (str_contains($amount, '.')) {
            $amount = rtrim(rtrim($amount, '0'), '.');
            if ($amount === '') {
                $amount = '0';
            }
        }

        $normalized = $sign === '-' ? '-'.$amount : $amount;

        return bcadd($normalized, '0', self::SCALE);
    }
}
