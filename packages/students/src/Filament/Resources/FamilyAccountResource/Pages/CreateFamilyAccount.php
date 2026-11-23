<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources\FamilyAccountResource\Pages;

use Cai\Students\Filament\Resources\FamilyAccountResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateFamilyAccount extends CreateRecord
{
    protected static string $resource = FamilyAccountResource::class;
}
