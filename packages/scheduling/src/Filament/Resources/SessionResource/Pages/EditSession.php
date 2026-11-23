<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources\SessionResource\Pages;

use Cai\Scheduling\Filament\Resources\SessionResource;
use Filament\Resources\Pages\EditRecord;

final class EditSession extends EditRecord
{
    protected static string $resource = SessionResource::class;
}
