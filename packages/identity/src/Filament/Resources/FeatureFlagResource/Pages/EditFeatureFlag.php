<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\FeatureFlagResource\Pages;

use Cai\Identity\Filament\Resources\FeatureFlagResource;
use Filament\Resources\Pages\EditRecord;

final class EditFeatureFlag extends EditRecord
{
    protected static string $resource = FeatureFlagResource::class;
}
