<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources;

use Cai\Students\Filament\Resources\GuardianResource\Pages;
use Cai\Students\Models\Guardian;
use Cai\Students\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Tutores y contactos (08.05 §2.5). Accesible a Recepción con
 * `students.guardians.manage`, sin permiso sobre el listado de alumnos.
 */
final class GuardianResource extends Resource
{
    protected static ?string $model = Guardian::class;

    protected static ?string $slug = 'guardians';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Tutores';

    protected static ?string $modelLabel = 'tutor';

    protected static ?string $pluralModelLabel = 'tutores';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('first_name')->label('Nombre')->required()->maxLength(100),
            Forms\Components\TextInput::make('last_name')->label('Apellido')->required()->maxLength(100),
            Forms\Components\TextInput::make('document_id')->label('Documento')->maxLength(30),
            Forms\Components\TextInput::make('phone')->label('Teléfono')->maxLength(40),
            Forms\Components\TextInput::make('email')->label('Email')->email()->maxLength(254),
            Forms\Components\TextInput::make('address')->label('Dirección')->maxLength(255),
            Forms\Components\TextInput::make('occupation')->label('Ocupación')->maxLength(120),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options(['active' => 'Activo', 'inactive' => 'Inactivo'])
                ->required()
                ->default('active'),
            Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
            Forms\Components\Placeholder::make('associated_students')
                ->label('Alumnos asociados')
                ->content(function (?Guardian $record): string {
                    if ($record === null || $record->students->isEmpty()) {
                        return '—';
                    }

                    return $record->students
                        ->map(fn (Student $student): string => $student->fullName().' ('.$student->student_code.')')
                        ->join(', ');
                })
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')->label('Apellido')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('document_id')->label('Documento')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Teléfono')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Activo', 'inactive' => 'Inactivo',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('last_name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuardians::route('/'),
            'create' => Pages\CreateGuardian::route('/create'),
            'edit' => Pages\EditGuardian::route('/{record}/edit'),
        ];
    }
}
