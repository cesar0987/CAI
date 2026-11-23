<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources\SessionResource\Pages;

use Cai\Scheduling\Filament\Resources\SessionResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSession extends CreateRecord
{
    protected static string $resource = SessionResource::class;
}
