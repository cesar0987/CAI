<?php

declare(strict_types=1);

namespace Cai\Care\Filament\Resources\ClinicalNoteResource\Pages;

use Cai\Care\Filament\Resources\ClinicalNoteResource;
use Filament\Resources\Pages\ListRecords;

final class ListClinicalNotes extends ListRecords
{
    protected static string $resource = ClinicalNoteResource::class;
}
