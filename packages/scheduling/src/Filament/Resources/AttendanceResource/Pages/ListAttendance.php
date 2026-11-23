<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources\AttendanceResource\Pages;

use Cai\Scheduling\Filament\Resources\AttendanceResource;
use Filament\Resources\Pages\ListRecords;

final class ListAttendance extends ListRecords
{
    protected static string $resource = AttendanceResource::class;
}
