<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\AuditEventResource\Pages;

use Cai\Identity\Filament\Resources\AuditEventResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewAuditEvent extends ViewRecord
{
    protected static string $resource = AuditEventResource::class;
}
