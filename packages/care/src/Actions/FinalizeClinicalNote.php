<?php

declare(strict_types=1);

namespace Cai\Care\Actions;

use Cai\Care\Data\FinalizeClinicalNoteData;
use Cai\Care\Enums\ClinicalNoteStatus;
use Cai\Care\Exceptions\NoteAlreadyFinalizedException;
use Cai\Care\Exceptions\NotNoteAuthorException;
use Cai\Care\Models\ClinicalNote;
use Cai\Identity\Audit\Auditor;
use Illuminate\Support\Facades\DB;

/** 08.07 §2.3: draft -> finalized. Escritura unica; despues es inmutable. */
final class FinalizeClinicalNote
{
    public function __construct(private readonly Auditor $auditor) {}

    public function execute(FinalizeClinicalNoteData $data): ClinicalNote
    {
        return DB::connection('care')->transaction(function () use ($data): ClinicalNote {
            $note = ClinicalNote::findOrFail($data->noteId);

            if ($note->status !== ClinicalNoteStatus::Draft->value) {
                throw new NoteAlreadyFinalizedException('La nota ya fue finalizada. Para corregirla, se crea una nota nueva.');
            }

            if ($note->author_user_id !== $data->actorUserId) {
                throw new NotNoteAuthorException('Solo el autor puede finalizar la nota.');
            }

            $note->update([
                'status' => ClinicalNoteStatus::Finalized->value,
                'finalized_at' => now(),
            ]);

            $this->auditor->record(
                'clinical_note.finalized',
                ClinicalNote::class,
                $note->id,
                ['student_id' => $note->student_id],
                $data->actorUserId,
                $note->student_id,
                'warning',
            );

            return $note->fresh();
        });
    }
}
