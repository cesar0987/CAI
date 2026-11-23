<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources\GuardianResource\Pages;

use Cai\Students\Filament\Resources\GuardianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListGuardians extends ListRecords
{
    protected static string $resource = GuardianResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
