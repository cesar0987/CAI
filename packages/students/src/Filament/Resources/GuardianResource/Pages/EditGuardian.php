<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources\GuardianResource\Pages;

use Cai\Students\Filament\Resources\GuardianResource;
use Filament\Resources\Pages\EditRecord;

final class EditGuardian extends EditRecord
{
    protected static string $resource = GuardianResource::class;
}
