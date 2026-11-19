<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\SettingResource\Pages;

use Cai\Identity\Filament\Resources\SettingResource;
use Filament\Resources\Pages\EditRecord;

final class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;
}
