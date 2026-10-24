<?php

declare(strict_types=1);

use Cai\Identity\Actions\AuthenticateUser;
use Cai\Identity\Data\AuthenticateUserData;
use Cai\Identity\Exceptions\InvalidCredentialsException;
use Illuminate\Support\Facades\Hash;
use Mockery;

it('accepts valid credentials and returns a successful authentication result', function () {
    Hash::shouldReceive('check')->once()->with('secret123', Mockery::type('string'))->andReturn(true);

    $result = app(AuthenticateUser::class)->execute(new AuthenticateUserData('demo@example.com', 'secret123'));

    expect($result->isSuccess())->toBeTrue();
    expect($result->unwrap()->user->email)->toBe('demo@example.com');
});

it('rejects invalid credentials with a domain exception', function () {
    Hash::shouldReceive('check')->once()->with('wrong', Mockery::type('string'))->andReturn(false);

    $result = app(AuthenticateUser::class)->execute(new AuthenticateUserData('demo@example.com', 'wrong'));

    expect($result->isFailure())->toBeTrue();
    expect($result->error())->toBeInstanceOf(InvalidCredentialsException::class);
});
