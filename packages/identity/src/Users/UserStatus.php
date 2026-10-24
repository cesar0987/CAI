<?php

declare(strict_types=1);

namespace Cai\Identity\Users;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';

    public static function validValues(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }
}
