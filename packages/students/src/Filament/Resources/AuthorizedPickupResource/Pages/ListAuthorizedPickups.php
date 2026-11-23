<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources\AuthorizedPickupResource\Pages;

use Cai\Students\Filament\Resources\AuthorizedPickupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListAuthorizedPickups extends ListRecords
{
    protected static string $resource = AuthorizedPickupResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
