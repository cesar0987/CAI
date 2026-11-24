<?php

declare(strict_types=1);

namespace Cai\Care\Filament\Resources;

use Cai\Care\Actions\FinalizeClinicalNote;
use Cai\Care\Actions\SupersedeClinicalNote;
use Cai\Care\Data\FinalizeClinicalNoteData;
use Cai\Care\Data\SupersedeClinicalNoteData;
use Cai\Care\Enums\ClinicalNoteStatus;
use Cai\Care\Enums\ClinicalNoteType;
use Cai\Care\Filament\Resources\ClinicalNoteResource\Pages;
use Cai\Care\Models\ClinicalNote;
use Cai\Scheduling\Models\Professional;
use Cai\Scheduling\Models\ProfessionalAssignment;
use Cai\Students\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

final class ClinicalNoteResource extends Resource
{
    protected static ?string $model = ClinicalNote::class;

    protected static ?string $slug = 'clinical-notes';

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $navigationLabel = 'Notas clínicas';

    protected static ?string $navigationGroup = 'Atención';

    protected static ?int $navigationSort = 30;

    /** 04.04 §6: el contenido clínico nunca entra en la búsqueda global. */
    protected static bool $isGloballySearchable = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->label('Alumno')
                ->options(self::assignedStudents())
                ->searchable()
                ->required(),
            Forms\Components\Select::make('note_type')
                ->label('Tipo')
                ->options(collect(ClinicalNoteType::cases())->mapWithKeys(fn (ClinicalNoteType $type): array => [$type->value => $type->name])->all())
                ->required(),
            Forms\Components\TextInput::make('summary')->label('Resumen')->maxLength(300),
            Forms\Components\Textarea::make('content')->label('Contenido')->required()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.student_code')->label('Código')->searchable(),
                Tables\Columns\TextColumn::make('student.first_name')->label('Nombre'),
                Tables\Columns\TextColumn::make('note_type')->label('Tipo')->badge(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge(),
                Tables\Columns\TextColumn::make('recorded_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('summary')->label('Resumen')->limit(80)->wrap(),
                // Nunca se incluye 'content': ni columna, ni búsqueda, ni exportación.
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('finalize')
                    ->label('Finalizar')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (ClinicalNote $record): bool => $record->status === ClinicalNoteStatus::Draft->value)
                    ->action(function (ClinicalNote $record): void {
                        app(FinalizeClinicalNote::class)->execute(new FinalizeClinicalNoteData($record->id, Auth::id()));
                    }),
                Tables\Actions\Action::make('supersede')
                    ->label('Corregir')
                    ->icon('heroicon-o-document-plus')
                    ->form([
                        Forms\Components\Textarea::make('newContent')->label('Contenido corregido')->required(),
                        Forms\Components\Textarea::make('correctionReason')->label('Motivo (mínimo 20 caracteres)')->required()->minLength(20),
                    ])
                    ->visible(fn (ClinicalNote $record): bool => $record->status !== ClinicalNoteStatus::Draft->value)
                    ->action(function (ClinicalNote $record, array $data): void {
                        app(SupersedeClinicalNote::class)->execute(new SupersedeClinicalNoteData(
                            $record->id,
                            Auth::id(),
                            $data['newContent'],
                            $data['correctionReason'],
                        ));
                    }),
            ])
            ->defaultSort('recorded_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return ClinicalNote::applyVisibleTo(parent::getEloquentQuery(), Auth::user());
    }

    /** @return array<int, string> */
    private static function assignedStudents(): array
    {
        $user = Auth::user();
        if ($user === null) {
            return [];
        }

        $professionalId = Professional::where('user_id', $user->id)->value('id');
        if ($professionalId === null) {
            return [];
        }

        $studentIds = ProfessionalAssignment::where('professional_id', $professionalId)
            ->whereNull('valid_until')
            ->pluck('student_id');

        return Student::whereIn('id', $studentIds)
            ->get()
            ->mapWithKeys(fn (Student $student): array => [$student->id => "{$student->student_code} — {$student->first_name} {$student->last_name}"])
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClinicalNotes::route('/'),
            'create' => Pages\CreateClinicalNote::route('/create'),
            'view' => Pages\ViewClinicalNote::route('/{record}'),
        ];
    }
}
