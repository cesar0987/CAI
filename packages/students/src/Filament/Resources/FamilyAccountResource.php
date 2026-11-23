<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources;

use Cai\Students\Filament\Resources\FamilyAccountResource\Pages;
use Cai\Students\Models\FamilyAccount;
use Cai\Students\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Cuenta familiar (ADR-0007): se muestran los hermanos vigentes y el historial
 * de pertenencia. El estado de cuenta llega con F6 y no se simula acá.
 */
final class FamilyAccountResource extends Resource
{
    protected static ?string $model = FamilyAccount::class;

    protected static ?string $slug = 'family-accounts';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Cuentas familiares';

    protected static ?string $modelLabel = 'cuenta familiar';

    protected static ?string $pluralModelLabel = 'cuentas familiares';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('account_code')->label('Código')->required()->maxLength(30),
            Forms\Components\TextInput::make('display_name')->label('Nombre visible')->required()->maxLength(180),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options(['active' => 'Activa', 'inactive' => 'Inactiva', 'closed' => 'Cerrada'])
                ->required()
                ->default('active'),
            Forms\Components\TextInput::make('currency')->label('Moneda')->default('PYG')->maxLength(10),
            Forms\Components\TextInput::make('address')->label('Dirección')->maxLength(255),
            Forms\Components\TextInput::make('phone')->label('Teléfono')->maxLength(40),
            Forms\Components\TextInput::make('email')->label('Email')->email()->maxLength(254),
            Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
            Forms\Components\Placeholder::make('current_students')
                ->label('Hermanos en la cuenta')
                ->content(function (?FamilyAccount $record): string {
                    if ($record === null || $record->students->isEmpty()) {
                        return '—';
                    }

                    return $record->students
                        ->map(fn (Student $student): string => $student->fullName().' ('.$student->student_code.')')
                        ->join(', ');
                })
                ->columnSpanFull(),
            Forms\Components\Placeholder::make('membership_history')
                ->label('Historial de pertenencia')
                ->content(function (?FamilyAccount $record): string {
                    if ($record === null || $record->memberships->isEmpty()) {
                        return '—';
                    }

                    return $record->memberships
                        ->map(fn ($membership): string => ($membership->student?->fullName() ?? 'Alumno')
                            .' · '.$membership->valid_from->format('d/m/Y')
                            .' → '.($membership->valid_until?->format('d/m/Y') ?? 'vigente'))
                        ->join('; ');
                })
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account_code')->label('Código')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('display_name')->label('Familia')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()->sortable(),
                Tables\Columns\TextColumn::make('currency')->label('Moneda'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Activa', 'inactive' => 'Inactiva', 'closed' => 'Cerrada',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('display_name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilyAccounts::route('/'),
            'create' => Pages\CreateFamilyAccount::route('/create'),
            'edit' => Pages\EditFamilyAccount::route('/{record}/edit'),
        ];
    }
}
