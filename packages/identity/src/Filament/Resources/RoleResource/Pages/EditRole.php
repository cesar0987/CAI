<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\RoleResource\Pages;

use Cai\Identity\Filament\Resources\RoleResource;
use Filament\Resources\Pages\EditRecord;

final class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;
}
