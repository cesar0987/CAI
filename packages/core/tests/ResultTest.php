<?php

declare(strict_types=1);

use Cai\Core\Support\Result;

it('represents successful and failure states without exceptions on the happy path', function () {
    $ok = Result::success(['id' => 42]);

    expect($ok->isSuccess())->toBeTrue();
    expect($ok->isFailure())->toBeFalse();
    expect($ok->value())->toMatchArray(['id' => 42]);
    expect($ok->unwrap())->toMatchArray(['id' => 42]);

    $fail = Result::failure(new RuntimeException('boom'));

    expect($fail->isSuccess())->toBeFalse();
    expect($fail->isFailure())->toBeTrue();
    expect($fail->error())->toBeInstanceOf(RuntimeException::class);
});

it('maps successful values and keeps the error in failure cases', function () {
    $mapped = Result::success(5)->map(fn (int $value) => $value * 2);
    expect($mapped->unwrap())->toBe(10);

    $failed = Result::failure(new RuntimeException('nope'))->map(fn ($value) => $value * 2);
    expect($failed->isFailure())->toBeTrue();
    expect($failed->error()->getMessage())->toBe('nope');
});
