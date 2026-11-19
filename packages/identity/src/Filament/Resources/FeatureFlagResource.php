<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources;

use Cai\Identity\Filament\Resources\FeatureFlagResource\Pages;
use Cai\Identity\Models\FeatureFlag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** Kill switches (06.04): los define el catálogo, no se crean ni se borran. */
final class FeatureFlagResource extends Resource
{
    protected static ?string $model = FeatureFlag::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Interruptores';

    protected static ?string $modelLabel = 'interruptor';

    protected static ?string $pluralModelLabel = 'interruptores';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')->label('Clave')->disabled()->dehydrated(),
            Forms\Components\TextInput::make('description')->label('Descripción')->disabled()->dehydrated(),
            Forms\Components\Select::make('scope')->label('Ámbito')->options([
                'module' => 'Módulo', 'feature' => 'Funcionalidad', 'experiment' => 'Experimento',
            ])->disabled()->dehydrated(),
            Forms\Components\Toggle::make('is_enabled')->label('Activo')->required(),
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->label('Clave')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Descripción'),
                Tables\Columns\TextColumn::make('scope')->label('Ámbito')->badge(),
                Tables\Columns\IconColumn::make('is_enabled')->label('Activo')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('key');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeatureFlags::route('/'),
            'edit' => Pages\EditFeatureFlag::route('/{record}/edit'),
        ];
    }
}
