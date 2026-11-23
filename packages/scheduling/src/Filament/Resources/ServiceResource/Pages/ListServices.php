<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources\ServiceResource\Pages;

use Cai\Scheduling\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
