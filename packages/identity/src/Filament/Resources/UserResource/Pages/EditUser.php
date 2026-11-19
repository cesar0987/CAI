<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\UserResource\Pages;

use Cai\Identity\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    // El trait Auditable del modelo registra user.updated con los cambios
    // (y redacta contraseñas/MFA). La página no duplica esa auditoría.
}
