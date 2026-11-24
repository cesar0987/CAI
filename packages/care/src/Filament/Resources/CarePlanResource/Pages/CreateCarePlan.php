<?php

declare(strict_types=1);

namespace Cai\Care\Filament\Resources\CarePlanResource\Pages;

use Cai\Care\Filament\Resources\CarePlanResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCarePlan extends CreateRecord
{
    protected static string $resource = CarePlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
