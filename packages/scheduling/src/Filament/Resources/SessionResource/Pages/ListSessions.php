<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources\SessionResource\Pages;

use Cai\Scheduling\Filament\Resources\SessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListSessions extends ListRecords
{
    protected static string $resource = SessionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
