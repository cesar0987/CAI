<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\RoleResource\Pages;

use Cai\Identity\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
