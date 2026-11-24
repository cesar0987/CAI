<?php

declare(strict_types=1);

namespace Cai\Care\Filament\Resources\CareGoalResource\Pages;

use Cai\Care\Filament\Resources\CareGoalResource;
use Filament\Resources\Pages\EditRecord;

final class EditCareGoal extends EditRecord
{
    protected static string $resource = CareGoalResource::class;
}
