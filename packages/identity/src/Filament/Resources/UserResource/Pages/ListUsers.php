<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\UserResource\Pages;

use Cai\Identity\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
