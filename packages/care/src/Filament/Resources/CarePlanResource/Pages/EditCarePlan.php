<?php

declare(strict_types=1);

namespace Cai\Care\Filament\Resources\CarePlanResource\Pages;

use Cai\Care\Filament\Resources\CarePlanResource;
use Filament\Resources\Pages\EditRecord;

final class EditCarePlan extends EditRecord
{
    protected static string $resource = CarePlanResource::class;
}
