<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources;

use Cai\Scheduling\Filament\Resources\ProfessionalResource\Pages;
use Cai\Scheduling\Models\Professional;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

final class ProfessionalResource extends Resource
{
    protected static ?string $model = Professional::class;

    protected static ?string $slug = 'professionals';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Profesionales';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('display_name')->label('Nombre en agenda')->required()->maxLength(200),
            Forms\Components\TextInput::make('specialty')->label('Especialidad')->maxLength(120),
            Forms\Components\TextInput::make('document_id')->label('Documento')->maxLength(30),
            Forms\Components\TextInput::make('license_number')->label('Matrícula')->maxLength(80),
            Forms\Components\TextInput::make('phone')->label('Teléfono')->maxLength(40),
            Forms\Components\TextInput::make('email')->label('Email')->email()->maxLength(254),
            Forms\Components\DatePicker::make('hired_at')->label('Fecha de ingreso')->native(false),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options(['active' => 'Activo', 'inactive' => 'Inactivo', 'on_leave' => 'De licencia'])
                ->required()
                ->default('active'),
            Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')->label('Profesional')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('specialty')->label('Especialidad')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label('Teléfono')->searchable(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('display_name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfessionals::route('/'),
            'create' => Pages\CreateProfessional::route('/create'),
            'edit' => Pages\EditProfessional::route('/{record}/edit'),
        ];
    }
}
