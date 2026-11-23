<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources\ProfessionalResource\Pages;

use Cai\Scheduling\Filament\Resources\ProfessionalResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateProfessional extends CreateRecord
{
    protected static string $resource = ProfessionalResource::class;
}
