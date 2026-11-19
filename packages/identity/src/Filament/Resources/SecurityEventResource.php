<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources;

use Cai\Identity\Filament\Resources\SecurityEventResource\Pages;
use Cai\Identity\Models\SecurityEvent;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** Eventos de seguridad: solo lectura (03.01 §8). */
final class SecurityEventResource extends Resource
{
    protected static ?string $model = SecurityEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Eventos de seguridad';

    protected static ?string $modelLabel = 'evento de seguridad';

    protected static ?string $pluralModelLabel = 'eventos de seguridad';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
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
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i:s')->sortable(),
                Tables\Columns\TextColumn::make('severity')->label('Severidad')->badge()->sortable(),
                Tables\Columns\TextColumn::make('event_type')->label('Evento')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user_id')->label('Usuario'),
                Tables\Columns\TextColumn::make('reason')->label('Motivo')->limit(50),
                Tables\Columns\TextColumn::make('ip_address')->label('IP'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')->options([
                    'info' => 'Info', 'warning' => 'Warning', 'critical' => 'Critical',
                ]),
            ])
            ->actions([Tables\Actions\ViewAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSecurityEvents::route('/'),
            'view' => Pages\ViewSecurityEvent::route('/{record}'),
        ];
    }
}
