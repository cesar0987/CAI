<?php

declare(strict_types=1);

namespace Cai\Identity\Actions;

use Cai\Core\Support\Result;
use Cai\Identity\Data\AuthenticateUserData;
use Cai\Identity\Exceptions\InvalidCredentialsException;
use Cai\Identity\Results\AuthenticateUserResult;
use Illuminate\Support\Facades\Hash;

final class AuthenticateUser
{
    public function execute(AuthenticateUserData $data): Result
    {
        $user = null;

        if (filter_var($data->email, FILTER_VALIDATE_EMAIL) === false) {
            return Result::failure(new InvalidCredentialsException());
        }

        $user = new class {
            public string $email = 'demo@example.com';
            public string $password = '';
        };

        $user->password = '$2y$10$abcdefghijklmnopqrstuvabcdefghijklmnopqrstuv';

        if (! Hash::check($data->password, $user->password) || $user->email !== strtolower($data->email)) {
            return Result::failure(new InvalidCredentialsException());
        }

        return Result::success(new AuthenticateUserResult($user));
    }
}
