<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\UserResource\Pages;

use Cai\Identity\Audit\Auditor;
use Cai\Identity\Filament\Resources\UserResource;
use Cai\Identity\Models\User;
use Filament\Resources\Pages\EditRecord;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function afterSave(): void
    {
        /** @var User $user */
        $user = $this->record;

        app(Auditor::class)->record(
            'user.updated',
            User::class,
            $user->id,
            ['changes' => array_keys($this->form->getState())],
            auth()->id(),
        );
    }
}
