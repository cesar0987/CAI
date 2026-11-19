<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\AuditEventResource\Pages;

use Cai\Identity\Audit\Auditor;
use Cai\Identity\Filament\Resources\AuditEventResource;
use Cai\Identity\Models\AuditEvent;
use Filament\Resources\Pages\ListRecords;

final class ListAuditEvents extends ListRecords
{
    protected static string $resource = AuditEventResource::class;

    public function mount(): void
    {
        parent::mount();

        // Lectura sensible auditada (04.05, gate F2): quién consultó la
        // auditoría queda registrado. Sin datos clínicos en el contexto.
        app(Auditor::class)->record(
            'audit.viewed',
            AuditEvent::class,
            null,
            ['resource' => 'audit_events'],
            auth()->id(),
            null,
            'warning',
        );
    }
}
