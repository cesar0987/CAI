<?php

declare(strict_types=1);

namespace Cai\Care\Actions;

use Cai\Care\Data\IssueClinicalNoteGrantData;
use Cai\Care\Exceptions\GrantExpiredException;
use Cai\Care\Exceptions\ReasonTooShortException;
use Cai\Care\Models\ClinicalNote;
use Cai\Care\Models\ClinicalNoteAccessGrant;
use Cai\Identity\Audit\Auditor;
use Cai\Identity\Models\SecurityEvent;
use Cai\Identity\Models\User;
use Illuminate\Support\Facades\DB;

/** 08.07 §2.6 / 04.04 §3.3: acceso excepcional con motivo y expiracion. */
final class IssueClinicalNoteGrant
{
    public function __construct(private readonly Auditor $auditor) {}

    public function execute(IssueClinicalNoteGrantData $data): ClinicalNoteAccessGrant
    {
        $actor = User::findOrFail($data->actorUserId);
        if (! $actor->hasPermissionTo('care.grants.issue')) {
            throw new \LogicException('Se necesita care.grants.issue para emitir un grant.');
        }

        if (mb_strlen(trim($data->reason)) < 20) {
            throw new ReasonTooShortException('El motivo del acceso excepcional debe tener al menos 20 caracteres.');
        }

        $maxDays = max(1, (int) config('cai.seguridad.clinical_grant_max_days', 30));
        $days = min(max(1, $data->days), $maxDays);
        $expiresAt = now()->addDays($days);
        if (! $expiresAt->isFuture()) {
            throw new GrantExpiredException('La expiración del grant debe ser futura.');
        }

        if ($data->clinicalNoteId !== null) {
            $note = ClinicalNote::findOrFail($data->clinicalNoteId);
            if ($note->student_id !== $data->studentId) {
                throw new \LogicException('El alumno del grant no coincide con el de la nota.');
            }
        }

        return DB::connection('care')->transaction(function () use ($data, $actor, $expiresAt): ClinicalNoteAccessGrant {
            $grant = ClinicalNoteAccessGrant::create([
                'clinical_note_id' => $data->clinicalNoteId,
                'student_id' => $data->studentId,
                'granted_to_user_id' => $data->grantedToUserId,
                'granted_by_user_id' => $actor->id,
                'reason' => $data->reason,
                'expires_at' => $expiresAt,
            ]);

            SecurityEvent::create([
                'event_type' => 'grant.issued',
                'severity' => 'warning',
                'user_id' => $data->grantedToUserId,
                'student_id' => $data->studentId,
                'resource_type' => ClinicalNote::class,
                'resource_id' => $data->clinicalNoteId,
                'reason' => $data->reason,
                'created_at' => now(),
            ]);

            $this->auditor->record(
                'clinical_note.grant.issued',
                ClinicalNoteAccessGrant::class,
                $grant->id,
                ['student_id' => $grant->student_id, 'expires_at' => $expiresAt->toIso8601String()],
                $actor->id,
                $grant->student_id,
                'warning',
            );

            return $grant;
        });
    }
}
