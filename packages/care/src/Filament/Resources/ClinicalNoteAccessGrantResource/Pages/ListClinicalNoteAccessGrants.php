<?php

declare(strict_types=1);

namespace Cai\Care\Filament\Resources\ClinicalNoteAccessGrantResource\Pages;

use Cai\Care\Filament\Resources\ClinicalNoteAccessGrantResource;
use Filament\Resources\Pages\ListRecords;

final class ListClinicalNoteAccessGrants extends ListRecords
{
    protected static string $resource = ClinicalNoteAccessGrantResource::class;
}
