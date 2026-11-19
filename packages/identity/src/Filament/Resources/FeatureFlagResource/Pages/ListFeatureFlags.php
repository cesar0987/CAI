<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources\FeatureFlagResource\Pages;

use Cai\Identity\Filament\Resources\FeatureFlagResource;
use Filament\Resources\Pages\ListRecords;

final class ListFeatureFlags extends ListRecords
{
    protected static string $resource = FeatureFlagResource::class;
}
