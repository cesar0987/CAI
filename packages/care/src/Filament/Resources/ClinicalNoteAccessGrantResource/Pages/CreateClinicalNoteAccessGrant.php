<?php

declare(strict_types=1);

namespace Cai\Care\Filament\Resources\ClinicalNoteAccessGrantResource\Pages;

use Cai\Care\Actions\IssueClinicalNoteGrant;
use Cai\Care\Data\IssueClinicalNoteGrantData;
use Cai\Care\Filament\Resources\ClinicalNoteAccessGrantResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class CreateClinicalNoteAccessGrant extends CreateRecord
{
    protected static string $resource = ClinicalNoteAccessGrantResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(IssueClinicalNoteGrant::class)->execute(new IssueClinicalNoteGrantData(
            actorUserId: Auth::id(),
            grantedToUserId: (int) $data['granted_to_user_id'],
            studentId: (int) $data['student_id'],
            reason: $data['reason'],
            days: (int) ($data['days'] ?? 7),
        ));
    }
}
