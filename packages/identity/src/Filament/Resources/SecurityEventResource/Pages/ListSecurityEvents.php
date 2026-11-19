<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\SecurityEventResource\Pages;

use Cai\Identity\Filament\Resources\SecurityEventResource;
use Filament\Resources\Pages\ListRecords;

final class ListSecurityEvents extends ListRecords
{
    protected static string $resource = SecurityEventResource::class;
}
