<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources\GroupResource\Pages;

use Cai\Scheduling\Filament\Resources\GroupResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateGroup extends CreateRecord
{
    protected static string $resource = GroupResource::class;
}
