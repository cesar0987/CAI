<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources\ServiceResource\Pages;

use Cai\Scheduling\Filament\Resources\ServiceResource;
use Filament\Resources\Pages\EditRecord;

final class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;
}
