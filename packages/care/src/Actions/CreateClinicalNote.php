<?php

declare(strict_types=1);

namespace Cai\Care\Actions;

use Cai\Care\Authorization\ClinicalScope;
use Cai\Care\Data\CreateClinicalNoteData;
use Cai\Care\Enums\ClinicalNoteStatus;
use Cai\Care\Enums\ClinicalNoteType;
use Cai\Care\Exceptions\ClinicalScopeException;
use Cai\Care\Models\ClinicalNote;
use Cai\Identity\Audit\Auditor;
use Cai\Identity\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Apertura de una nota en borrador (08.07 §2.3).
 * El actor explicito en el DTO evita depender de Auth en la capa de dominio.
 */
final class CreateClinicalNote
{
    public function __construct(
        private readonly Auditor $auditor,
        private readonly ClinicalScope $scope,
    ) {}

    public function execute(CreateClinicalNoteData $data): ClinicalNote
    {
        $actor = User::findOrFail($data->actorUserId);

        if (! $actor->hasPermissionTo('care.notes.create')
            || ! $this->scope->hasActiveAssignmentFor($actor, $data->studentId)) {
            throw new ClinicalScopeException('Se necesita care.notes.create y una asignacion vigente sobre el alumno.');
        }

        $noteType = ClinicalNoteType::tryFrom($data->noteType);
        if ($noteType === null) {
            throw new \InvalidArgumentException("Tipo de nota desconocido: {$data->noteType}");
        }

        return DB::connection('care')->transaction(function () use ($data, $actor, $noteType): ClinicalNote {
            $note = ClinicalNote::create([
                'student_id' => $data->studentId,
                'author_user_id' => $actor->id,
                'session_id' => $data->sessionId,
                'care_plan_id' => $data->carePlanId,
                'note_type' => $noteType->value,
                'status' => ClinicalNoteStatus::Draft->value,
                'content' => $data->content,
                'summary' => $data->summary,
                'recorded_at' => now(),
            ]);

            $this->auditor->record(
                'clinical_note.created',
                ClinicalNote::class,
                $note->id,
                ['student_id' => $note->student_id, 'note_type' => $noteType->value],
                $actor->id,
                $note->student_id,
                'warning',
            );

            return $note;
        });
    }
}
