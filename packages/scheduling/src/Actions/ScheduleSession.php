<?php

declare(strict_types=1);

namespace Cai\Scheduling\Actions;

use Cai\Scheduling\Data\ScheduleSessionData;
use Cai\Scheduling\Enums\SessionStatus;
use Cai\Scheduling\Exceptions\CapacityExceededException;
use Cai\Scheduling\Exceptions\OverlappingSessionException;
use Cai\Scheduling\Models\Group;
use Cai\Scheduling\Models\Session;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Programa una sesión con participantes y profesionales en una transacción.
 * El chequeo de superposición es la optimización para SQLite; en PostgreSQL
 * la garantía dura es el EXCLUDE sobre session_professionals (03.04 §4).
 */
final class ScheduleSession
{
    public function execute(ScheduleSessionData $data): Session
    {
        return DB::transaction(function () use ($data): Session {
            foreach ($data->professionals as $professional) {
                $overlap = Session::query()
                    ->whereHas(
                        'professionals',
                        fn (Builder $query): Builder => $query->where('professionals.id', $professional['id']),
                    )
                    ->where('status', SessionStatus::Scheduled->value)
                    ->where('starts_at', '<', $data->endsAt)
                    ->where('ends_at', '>', $data->startsAt)
                    ->exists();

                if ($overlap) {
                    throw new OverlappingSessionException(
                        "El profesional {$professional['id']} ya tiene una sesión en ese rango horario.",
                    );
                }
            }

            if ($data->groupId !== null) {
                $group = Group::findOrFail($data->groupId);
                if (count($data->participants) > $group->capacity) {
                    throw new CapacityExceededException(
                        "El grupo {$group->name} tiene capacidad para {$group->capacity} alumnos.",
                    );
                }
            }

            $session = Session::create([
                'service_id' => $data->serviceId,
                'group_id' => $data->groupId,
                'session_type' => $data->sessionType,
                'starts_at' => $data->startsAt,
                'ends_at' => $data->endsAt,
                'status' => SessionStatus::Scheduled->value,
                'notes' => $data->notes,
                'created_by' => $data->createdBy,
            ]);

            foreach ($data->participants as $studentId) {
                $session->participants()->create(['student_id' => $studentId]);
            }

            foreach ($data->professionals as $professional) {
                $session->professionals()->attach($professional['id'], [
                    'assignment_role' => $professional['role'],
                    'starts_at' => $data->startsAt,
                    'ends_at' => $data->endsAt,
                ]);
            }

            $session->refresh();

            return $session;
        });
    }
}
