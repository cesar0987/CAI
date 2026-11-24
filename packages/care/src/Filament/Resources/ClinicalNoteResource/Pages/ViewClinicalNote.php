<?php

declare(strict_types=1);

namespace Cai\Care\Filament\Resources\ClinicalNoteResource\Pages;

use Cai\Care\Audit\ClinicalNoteReadAudit;
use Cai\Care\Filament\Resources\ClinicalNoteResource;
use Cai\Care\Models\ClinicalNote;
use Cai\Identity\Models\User;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Gate;

final class ViewClinicalNote extends ViewRecord
{
    protected static string $resource = ClinicalNoteResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var ClinicalNote $note */
        $note = $this->record;
        Gate::authorize('view', $note);

        /** @var User $actor */
        $actor = auth()->user();
        app(ClinicalNoteReadAudit::class)->record($note, $actor);
    }
}
