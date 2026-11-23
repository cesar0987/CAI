<?php

declare(strict_types=1);

namespace Cai\Scheduling\Actions;

use Cai\Identity\Audit\Auditor;
use Cai\Scheduling\Data\RecordAttendanceData;
use Cai\Scheduling\Enums\AttendanceStatus;
use Cai\Scheduling\Exceptions\ParticipantNotFoundException;
use Cai\Scheduling\Models\AttendanceRecord;
use Cai\Scheduling\Models\Session;
use Cai\Scheduling\Results\AttendanceSummary;
use Illuminate\Support\Facades\DB;

/**
 * Registro y corrección de asistencia (03.04 §7, 08.06 §2.5).
 * Una corrección actualiza la fila y deja el diff en audit_events.
 */
final class RecordAttendance
{
    public function __construct(private readonly Auditor $auditor) {}

    public function execute(RecordAttendanceData $data): AttendanceSummary
    {
        return DB::transaction(function () use ($data): AttendanceSummary {
            $session = Session::with('participants')->findOrFail($data->sessionId);

            foreach ($data->records as $record) {
                $participant = $session->participants
                    ->firstWhere('student_id', $record['participant_student_id']);

                if ($participant === null) {
                    throw new ParticipantNotFoundException(
                        "El alumno {$record['participant_student_id']} no participa de la sesión.",
                    );
                }

                $status = AttendanceStatus::tryFrom($record['status']);
                if ($status === null) {
                    throw new \InvalidArgumentException("Estado de asistencia desconocido: {$record['status']}");
                }

                $justification = $record['justification'] ?? null;
                if ($status === AttendanceStatus::Excused && ($justification === null || $justification === '')) {
                    throw new \InvalidArgumentException('Una ausencia autorizada requiere justificación.');
                }

                $existing = AttendanceRecord::where('participant_id', $participant->id)->first();

                if ($existing !== null && $existing->status !== $status->value) {
                    $this->auditor->record(
                        'attendance.corrected',
                        AttendanceRecord::class,
                        $existing->id,
                        ['from' => $existing->status, 'to' => $status->value],
                        $data->recordedBy,
                        $participant->student_id,
                        'warning',
                    );
                }

                AttendanceRecord::updateOrCreate(
                    ['participant_id' => $participant->id],
                    [
                        'status' => $status->value,
                        'recorded_by' => $data->recordedBy,
                        'recorded_at' => now(),
                        'notes' => $justification,
                    ],
                );
            }

            return new AttendanceSummary($session->id, count($data->records));
        });
    }
}
