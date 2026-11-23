<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources\ProfessionalResource\Pages;

use Cai\Scheduling\Filament\Resources\ProfessionalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListProfessionals extends ListRecords
{
    protected static string $resource = ProfessionalResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
