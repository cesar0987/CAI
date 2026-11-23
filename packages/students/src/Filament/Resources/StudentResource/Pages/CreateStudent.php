<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources\StudentResource\Pages;

use Cai\Students\Filament\Resources\StudentResource;
use Cai\Students\Support\StudentCodeGenerator;
use Filament\Resources\Pages\CreateRecord;

final class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // El código nace del generador de dominio (ADR-0020), no del formulario.
        $data['student_code'] = app(StudentCodeGenerator::class)->generate((int) now()->year);

        $userId = auth()->id();
        if ($userId !== null) {
            $data['created_by'] = (int) $userId;
        }

        return $data;
    }
}
