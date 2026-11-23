<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources\AuthorizedPickupResource\Pages;

use Cai\Students\Filament\Resources\AuthorizedPickupResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateAuthorizedPickup extends CreateRecord
{
    protected static string $resource = AuthorizedPickupResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $userId = auth()->id();
        if ($userId !== null) {
            $data['created_by'] = (int) $userId;
        }

        return $data;
    }
}
