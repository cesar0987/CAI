<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\SettingResource\Pages;

use Cai\Identity\Filament\Resources\SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
