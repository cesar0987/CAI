<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\SecurityEventResource\Pages;

use Cai\Identity\Audit\Auditor;
use Cai\Identity\Filament\Resources\SecurityEventResource;
use Cai\Identity\Models\SecurityEvent;
use Filament\Resources\Pages\ListRecords;

final class ListSecurityEvents extends ListRecords
{
    protected static string $resource = SecurityEventResource::class;

    public function mount(): void
    {
        parent::mount();

        // Lectura sensible auditada (04.05).
        app(Auditor::class)->record(
            'security_events.viewed',
            SecurityEvent::class,
            null,
            ['resource' => 'security_events'],
            auth()->id(),
            null,
            'warning',
        );
    }
}
