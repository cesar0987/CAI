<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources\FamilyAccountResource\Pages;

use Cai\Students\Filament\Resources\FamilyAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListFamilyAccounts extends ListRecords
{
    protected static string $resource = FamilyAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
