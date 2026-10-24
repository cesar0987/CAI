<?php

declare(strict_types=1);

namespace Cai\Identity\PasswordPolicy;

use Cai\Identity\Exceptions\PasswordChangeException;
use Cai\Core\Support\Result;

final class PasswordChangeService
{
    public function changePassword(PasswordChangeCommand $command, bool $currentPasswordMatches): Result
    {
        if (! $currentPasswordMatches) {
            return Result::failure(new PasswordChangeException('La contraseña actual no coincide.'));
        }

        if (strlen($command->newPassword) < 12) {
            return Result::failure(new PasswordChangeException('La nueva contraseña debe tener al menos 12 caracteres.'));
        }

        if ($command->currentPassword === $command->newPassword) {
            return Result::failure(new PasswordChangeException('La nueva contraseña debe ser distinta a la actual.'));
        }

        return Result::success(true);
    }
}
