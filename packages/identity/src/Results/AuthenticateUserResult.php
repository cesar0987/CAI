<?php

declare(strict_types=1);

namespace Cai\Identity\Results;

final readonly class AuthenticateUserResult
{
    public function __construct(
        public mixed $user,
    ) {
    }
}
