<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources\EnrollmentResource\Pages;

use Cai\Scheduling\Filament\Resources\EnrollmentResource;
use Filament\Resources\Pages\EditRecord;

final class EditEnrollment extends EditRecord
{
    protected static string $resource = EnrollmentResource::class;
}
