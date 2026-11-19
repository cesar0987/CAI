<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources;

use Cai\Identity\Filament\Resources\RoleResource\Pages;
use Cai\Identity\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

final class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Roles y permisos';

    protected static ?string $modelLabel = 'rol';

    protected static ?string $pluralModelLabel = 'roles';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Código')
                ->required()
                ->unique(ignoreRecord: true)
                ->disabled(fn (string $operation): bool => $operation === 'edit')
                ->dehydrated(),
            Forms\Components\TextInput::make('label')->label('Nombre')->required()->maxLength(120),
            Forms\Components\Select::make('permissions')
                ->label('Permisos (matriz 04.02)')
                ->multiple()
                ->relationship('permissions', 'name')
                ->preload()
                ->searchable()
                ->helperText('Los cambios se auditan; Dirección revisa los permisos sensibles.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Código')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('label')->label('Nombre')->searchable(),
                Tables\Columns\TextColumn::make('permissions_count')->label('Permisos')->counts('permissions')->sortable(),
                Tables\Columns\TextColumn::make('users_count')->label('Usuarios')->counts('users'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
