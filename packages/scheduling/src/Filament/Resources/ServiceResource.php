<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources;

use Cai\Scheduling\Filament\Resources\ServiceResource\Pages;
use Cai\Scheduling\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

final class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $slug = 'services';

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationLabel = 'Servicios';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(150),
            Forms\Components\Textarea::make('description')->label('Descripción')->columnSpanFull(),
            Forms\Components\TextInput::make('default_duration_minutes')->label('Duración por defecto (min)')->numeric()->minValue(1),
            Forms\Components\Select::make('modality')
                ->label('Modalidad')
                ->options(['individual' => 'Individual', 'group' => 'Grupal', 'both' => 'Ambas'])
                ->required()
                ->default('both'),
            Forms\Components\TextInput::make('color')->label('Color del calendario')->maxLength(7),
            Forms\Components\TextInput::make('sort_order')->label('Orden')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Activo')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('modality')->label('Modalidad')->badge(),
                Tables\Columns\TextColumn::make('default_duration_minutes')->label('Duración'),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
