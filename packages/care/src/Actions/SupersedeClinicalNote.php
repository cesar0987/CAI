<?php

declare(strict_types=1);

namespace Cai\Care\Actions;

use Cai\Care\Authorization\ClinicalScope;
use Cai\Care\Data\SupersedeClinicalNoteData;
use Cai\Care\Enums\ClinicalNoteStatus;
use Cai\Care\Exceptions\ClinicalScopeException;
use Cai\Care\Exceptions\NotFinalizedException;
use Cai\Care\Exceptions\ReasonTooShortException;
use Cai\Care\Models\ClinicalNote;
use Cai\Identity\Audit\Auditor;
use Cai\Identity\Models\User;
use Illuminate\Support\Facades\DB;

/** 08.07 §2.4: la correccion nunca editla original; crea una nota nueva. */
final class SupersedeClinicalNote
{
    public function __construct(
        private readonly Auditor $auditor,
        private readonly ClinicalScope $scope,
    ) {}

    public function execute(SupersedeClinicalNoteData $data): ClinicalNote
    {
        $actor = User::findOrFail($data->actorUserId);

        if (mb_strlen(trim($data->correctionReason)) < 20) {
            throw new ReasonTooShortException('El motivo de la corrección debe tener al menos 20 caracteres.');
        }

        return DB::connection('care')->transaction(function () use ($data, $actor): ClinicalNote {
            $original = ClinicalNote::findOrFail($data->noteId);

            if ($original->status === ClinicalNoteStatus::Draft->value) {
                throw new NotFinalizedException('Una nota en borrador se edita, no se corrige.');
            }

            if ($original->author_user_id !== $actor->id
                && ! $this->scope->hasActiveAssignmentFor($actor, $original->student_id, $original->recorded_at)) {
                throw new ClinicalScopeException('Solo el autor o un profesional asignado puede corregir la nota.');
            }

            $correction = ClinicalNote::create([
                'student_id' => $original->student_id,
                'author_user_id' => $actor->id,
                'session_id' => $original->session_id,
                'care_plan_id' => $original->care_plan_id,
                'note_type' => $original->note_type,
                'status' => ClinicalNoteStatus::Finalized->value,
                'content' => $data->newContent,
                'summary' => $data->newSummary ?? $original->summary,
                'supersedes_note_id' => $original->id,
                'correction_reason' => $data->correctionReason,
                'recorded_at' => now(),
                'finalized_at' => now(),
            ]);

            $original->update(['status' => ClinicalNoteStatus::Superseded->value]);

            $this->auditor->record(
                'clinical_note.superseded',
                ClinicalNote::class,
                $original->id,
                ['student_id' => $original->student_id, 'context' => ['correction_note_id' => $correction->id]],
                $actor->id,
                $original->student_id,
                'warning',
            );

            return $correction;
        });
    }
}
