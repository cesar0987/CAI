<?php

declare(strict_types=1);

namespace Cai\Scheduling\Actions;

use Cai\Scheduling\Data\GenerateRecurringSessionsData;
use Cai\Scheduling\Data\ScheduleSessionData;
use Cai\Scheduling\Exceptions\OverlappingSessionException;
use Cai\Scheduling\Models\AvailabilityRule;
use Cai\Scheduling\Models\Session;

/**
 * Genera sesiones desde las reglas semanales (08.06 §2.4). Es idempotente:
 * una sesión ya existente en la misma fecha y servicio no se duplica.
 */
final class GenerateRecurringSessions
{
    public function __construct(private readonly ScheduleSession $scheduler) {}

    public function execute(GenerateRecurringSessionsData $data): int
    {
        $created = 0;
        $cursor = $data->from;

        while ($cursor->lessThanOrEqualTo($data->to)) {
            foreach (AvailabilityRule::query()->get() as $rule) {
                if (! $rule->isVigente() || ! $rule->occursOn($cursor)) {
                    continue;
                }

                $startsAt = $cursor->setTimeFromTimeString($rule->start_time);
                $endsAt = $cursor->setTimeFromTimeString($rule->end_time);

                $exists = Session::query()
                    ->where('service_id', $data->serviceId)
                    ->where('starts_at', $startsAt)
                    ->exists();

                if ($exists) {
                    continue;
                }

                try {
                    $this->scheduler->execute(new ScheduleSessionData(
                        serviceId: $data->serviceId,
                        startsAt: $startsAt,
                        endsAt: $endsAt,
                        professionals: [[
                            'id' => (int) $rule->professional_id,
                            'role' => 'responsible',
                        ]],
                        sessionType: 'individual',
                        createdBy: $data->createdBy,
                    ));
                    $created++;
                } catch (OverlappingSessionException) {
                    // Otra regla ya cubre ese rango: el generador sigue siendo idempotente.
                }
            }

            $cursor = $cursor->addDay();
        }

        return $created;
    }
}
