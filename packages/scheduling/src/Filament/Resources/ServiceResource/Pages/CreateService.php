<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources\ServiceResource\Pages;

use Cai\Scheduling\Filament\Resources\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;
}
