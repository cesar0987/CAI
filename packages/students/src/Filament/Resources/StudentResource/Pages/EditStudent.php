<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources\StudentResource\Pages;

use Cai\Students\Filament\Resources\StudentResource;
use Filament\Resources\Pages\EditRecord;

final class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;
}
