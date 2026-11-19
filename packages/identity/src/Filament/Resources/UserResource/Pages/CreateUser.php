<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\UserResource\Pages;

use Cai\Identity\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Cuenta creada por un administrador: la rotación empieza hoy.
        $data['password_changed_at'] = now();

        return parent::handleRecordCreation($data);
    }
}
