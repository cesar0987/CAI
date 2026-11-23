<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources;

use Cai\Scheduling\Filament\Resources\GroupResource\Pages;
use Cai\Scheduling\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

final class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $slug = 'groups';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Grupos';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('service_id')->label('Servicio')->relationship('service', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(150),
            Forms\Components\Select::make('responsible_professional_id')
                ->label('Profesional responsable')
                ->relationship('responsible', 'display_name')
                ->searchable()
                ->preload()
                ->nullable(),
            Forms\Components\TextInput::make('room')->label('Sala')->maxLength(80),
            Forms\Components\TextInput::make('capacity')->label('Capacidad')->numeric()->minValue(1)->required(),
            Forms\Components\TextInput::make('max_duration_minutes')->label('Duración máxima (min)')->numeric()->default(60),
            Forms\Components\Toggle::make('is_active')->label('Activo')->default(true),
            Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Grupo')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('service.name')->label('Servicio')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('responsible.display_name')->label('Responsable'),
                Tables\Columns\TextColumn::make('capacity')->label('Capacidad'),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
