<?php

declare(strict_types=1);

namespace Cai\Identity\Data;

final readonly class AuthenticateUserData
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }
}
