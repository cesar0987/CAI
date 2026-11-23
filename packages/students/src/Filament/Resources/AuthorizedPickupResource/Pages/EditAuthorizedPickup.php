<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources\AuthorizedPickupResource\Pages;

use Cai\Students\Filament\Resources\AuthorizedPickupResource;
use Filament\Resources\Pages\EditRecord;

final class EditAuthorizedPickup extends EditRecord
{
    protected static string $resource = AuthorizedPickupResource::class;
}
