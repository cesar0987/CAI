<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources\GuardianResource\Pages;

use Cai\Students\Filament\Resources\GuardianResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateGuardian extends CreateRecord
{
    protected static string $resource = GuardianResource::class;
}
