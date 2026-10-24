<?php

declare(strict_types=1);

use Cai\Core\Support\Money;

it('keeps exact decimal arithmetic for money values', function () {
    $a = Money::parse('0.10');
    $b = Money::parse('0.20');

    expect($a->plus($b)->value())->toBe('0.30');
    expect($a->minus($b)->value())->toBe('-0.10');
    expect($a->plus($b)->isGreaterThan(Money::zero()))->toBeTrue();
});

it('can format and compare money values without floating-point drift', function () {
    $sum = Money::parse('0.10')->plus(Money::parse('0.20'))->plus(Money::parse('0.30'));

    expect($sum->value())->toBe('0.60');
    expect(Money::format($sum))->toBe('0,60');
    expect($sum->compare(Money::parse('0.50')))->toBe(1);
    expect(Money::zero()->isZero())->toBeTrue();
});

it('rejects invalid money input', function () {
    Money::parse('abc');
})->throws(InvalidArgumentException::class);
