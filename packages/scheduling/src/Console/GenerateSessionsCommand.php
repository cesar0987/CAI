<?php

declare(strict_types=1);

namespace Cai\Scheduling\Console;

use Cai\Scheduling\Actions\GenerateRecurringSessions;
use Cai\Scheduling\Data\GenerateRecurringSessionsData;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

/**
 *   php artisan cai:sessions:generate --service=1 --weeks=4
 *
 * El job es idempotente: puede correrse a diario sin duplicar sesiones.
 */
final class GenerateSessionsCommand extends Command
{
    protected $signature = 'cai:sessions:generate
        {--service= : ID del servicio cuyas reglas se generan}
        {--weeks=4 : Semanas hacia adelante}
        {--from= : Fecha de inicio ISO (por defecto, inicio de semana)}';

    protected $description = 'Genera sesiones recurrentes desde las reglas semanales de disponibilidad';

    public function handle(GenerateRecurringSessions $generator): int
    {
        $serviceId = (int) $this->option('service');
        if ($serviceId <= 0) {
            $this->error('Debe indicar --service con el ID del servicio.');

            return self::FAILURE;
        }

        $fromOption = $this->option('from');
        $from = $fromOption !== null && $fromOption !== ''
            ? CarbonImmutable::parse((string) $fromOption)->startOfDay()
            : now()->startOfWeek()->toImmutable();

        $to = $from->addWeeks((int) $this->option('weeks'));

        $created = $generator->execute(new GenerateRecurringSessionsData(
            serviceId: $serviceId,
            from: $from,
            to: $to,
            createdBy: auth()->id() !== null ? (int) auth()->id() : null,
        ));

        $this->info("Sesiones creadas: {$created} entre {$from->toDateString()} y {$to->toDateString()}.");

        return self::SUCCESS;
    }
}
