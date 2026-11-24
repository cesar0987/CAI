<?php

declare(strict_types=1);

namespace Cai\Care\Audit;

use Cai\Care\Models\ClinicalNote;
use Cai\Identity\Audit\Auditor;
use Cai\Identity\Models\User;

/**
 * Auditoría de lectura sensible (04.05 / 08.07 §2.7).
 * Se invoca explícitamente desde la página de detalle: un observer global
 * `retrieved` auditaría también las cargas de relación y llenaría la tabla.
 */
final class ClinicalNoteReadAudit
{
    public function __construct(private readonly Auditor $auditor) {}

    public function record(ClinicalNote $note, User $actor): void
    {
        $this->auditor->record(
            'clinical_note.viewed',
            ClinicalNote::class,
            $note->id,
            ['student_id' => $note->student_id, 'context' => ['note_type' => $note->note_type]],
            $actor->id,
            $note->student_id,
            'warning',
        );
    }
}
