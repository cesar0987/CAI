<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources;

use Cai\Identity\Filament\Resources\SettingResource\Pages;
use Cai\Identity\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** Configuración del centro (03.01 §5). Los settings no se borran. */
final class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Configuración';

    protected static ?string $modelLabel = 'ajuste';

    protected static ?string $pluralModelLabel = 'ajustes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')->label('Clave')->required()->unique(ignoreRecord: true)->disabled(fn (string $operation): bool => $operation === 'edit')->dehydrated(),
            Forms\Components\Textarea::make('value')->label('Valor (JSON)')->required()->rows(4),
            Forms\Components\TextInput::make('type')->label('Tipo')->required()->default('string'),
            Forms\Components\TextInput::make('group_name')->label('Grupo')->required()->default('general'),
            Forms\Components\Toggle::make('is_public')->label('Público'),
            Forms\Components\TextInput::make('description')->label('Descripción')->maxLength(255),
        ]);
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
                Tables\Columns\TextColumn::make('group_name')->label('Grupo')->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Tipo'),
                Tables\Columns\IconColumn::make('is_public')->label('Público')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('key');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
