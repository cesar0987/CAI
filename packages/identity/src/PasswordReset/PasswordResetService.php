<?php

declare(strict_types=1);

namespace Cai\Identity\PasswordReset;

use Cai\Core\Support\Result;
use Cai\Identity\Exceptions\PasswordResetException;

final class PasswordResetService
{
    public function resetPassword(PasswordResetRequest $request, PasswordResetToken $token, bool $tokenMatchesEmail): Result
    {
        if (! $tokenMatchesEmail || $request->email !== $token->email) {
            return Result::failure(new PasswordResetException('El token no corresponde al email del usuario.'));
        }

        if (! $token->isUsable()) {
            return Result::failure(new PasswordResetException('El token de recuperación no es válido o ha expirado.'));
        }

        return Result::success(true);
    }
}
