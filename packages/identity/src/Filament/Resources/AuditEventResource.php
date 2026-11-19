<?php

declare(strict_types=1);

namespace Cai\Identity\Filament\Resources;

use Cai\Identity\Filament\Resources\AuditEventResource\Pages;
use Cai\Identity\Models\AuditEvent;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Auditoría: estrictamente de lectura (04.05, 08.04 §2.6).
 * No se crea, no se edita, no se borra. Ni siquiera Dirección.
 */
final class AuditEventResource extends Resource
{
    protected static ?string $model = AuditEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Auditoría';

    protected static ?string $modelLabel = 'evento de auditoría';

    protected static ?string $pluralModelLabel = 'eventos de auditoría';

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
                Tables\Columns\TextColumn::make('actor_role')->label('Rol')->badge()->searchable(),
                Tables\Columns\TextColumn::make('action')->label('Acción')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('entity_type')->label('Entidad'),
                Tables\Columns\TextColumn::make('entity_id')->label('ID'),
                Tables\Columns\TextColumn::make('student_id')->label('Alumno'),
                Tables\Columns\TextColumn::make('ip_address')->label('IP'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')->options(fn () => AuditEvent::query()
                    ->distinct()
                    ->orderBy('action')
                    ->pluck('action', 'action')
                    ->all()),
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
            'index' => Pages\ListAuditEvents::route('/'),
            'view' => Pages\ViewAuditEvent::route('/{record}'),
        ];
    }
}
