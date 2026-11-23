<?php

declare(strict_types=1);

namespace Cai\Scheduling\Actions;

use Cai\Scheduling\Data\RescheduleSessionData;
use Cai\Scheduling\Data\ScheduleSessionData;
use Cai\Scheduling\Enums\SessionStatus;
use Cai\Scheduling\Models\Session;
use Illuminate\Support\Facades\DB;

/**
 * Reprogramar NO borra: crea una sesión nueva enlazada a la original y marca
 * la original como `rescheduled` (03.04 §8, 08.06 §2.6).
 */
final class RescheduleSession
{
    public function __construct(private readonly ScheduleSession $scheduler) {}

    public function execute(RescheduleSessionData $data): Session
    {
        $original = Session::with('participants')->findOrFail($data->sessionId);
        $pivotRows = DB::table('session_professionals')
            ->where('session_id', $original->id)
            ->get(['professional_id', 'assignment_role']);

        $new = $this->scheduler->execute(new ScheduleSessionData(
            serviceId: $original->service_id,
            startsAt: $data->newStartsAt,
            endsAt: $data->newEndsAt,
            participants: array_map('intval', $original->participants->pluck('student_id')->all()),
            professionals: $pivotRows
                ->map(fn ($row): array => [
                    'id' => (int) $row->professional_id,
                    'role' => (string) $row->assignment_role,
                ])
                ->all(),
            groupId: $original->group_id,
            sessionType: $original->session_type,
            notes: $data->reason,
            createdBy: $data->createdBy,
        ));

        DB::transaction(function () use ($original, $new): void {
            $new->update(['rescheduled_from_id' => $original->id]);
            $original->update(['status' => SessionStatus::Rescheduled->value]);
        });

        $new->refresh();

        return $new;
    }
}
