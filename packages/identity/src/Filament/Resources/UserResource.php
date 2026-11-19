<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources;

use Cai\Identity\Filament\Resources\UserResource\Pages;
use Cai\Identity\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'usuario';

    protected static ?string $pluralModelLabel = 'usuarios';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(150),
            Forms\Components\TextInput::make('email')->label('Email')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('password')
                ->label('Contraseña provisional')
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->minLength(12)
                ->visible(fn (string $operation): bool => $operation === 'create'),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options(['active' => 'Activo', 'inactive' => 'Inactivo', 'suspended' => 'Suspendido'])
                ->required()
                ->default('active'),
            Forms\Components\Select::make('roles')
                ->label('Roles')
                ->multiple()
                ->relationship('roles', 'name')
                ->preload()
                ->searchable()
                ->helperText('Cambios de rol quedan auditados. Los roles se gestionan desde la matriz 04.02.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()->sortable(),
                Tables\Columns\TextColumn::make('roles.name')->label('Roles')->badge()->searchable(),
                Tables\Columns\TextColumn::make('last_login_at')->label('Último ingreso')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('mfa_enabled_at')->label('MFA')->dateTime('d/m/Y')->placeholder('Sin activar'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Activo', 'inactive' => 'Inactivo', 'suspended' => 'Suspendido',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
