<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\RoleResource\Pages;

use Cai\Identity\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
}
