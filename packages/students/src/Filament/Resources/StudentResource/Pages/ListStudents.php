<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources\StudentResource\Pages;

use Cai\Students\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
