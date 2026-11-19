<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\AuditEventResource\Pages;

use Cai\Identity\Filament\Resources\AuditEventResource;
use Filament\Resources\Pages\ListRecords;

final class ListAuditEvents extends ListRecords
{
    protected static string $resource = AuditEventResource::class;
}
