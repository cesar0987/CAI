<?php

declare(strict_types=1);

namespace Cai\Care\Filament\Resources\ClinicalNoteResource\Pages;

use Cai\Care\Actions\CreateClinicalNote as CreateClinicalNoteAction;
use Cai\Care\Data\CreateClinicalNoteData;
use Cai\Care\Filament\Resources\ClinicalNoteResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class CreateClinicalNote extends CreateRecord
{
    protected static string $resource = ClinicalNoteResource::class;

    /** Toda la creación pasa por la Action: centro de permiso y alcance. */
    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateClinicalNoteAction::class)->execute(new CreateClinicalNoteData(
            actorUserId: Auth::id(),
            studentId: (int) $data['student_id'],
            noteType: $data['note_type'],
            content: $data['content'],
            summary: $data['summary'] ?? null,
        ));
    }
}
