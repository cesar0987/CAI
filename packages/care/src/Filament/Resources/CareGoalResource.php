<?php

declare(strict_types=1);

namespace Cai\Care\Filament\Resources;

use Cai\Care\Filament\Resources\CareGoalResource\Pages;
use Cai\Care\Models\CareGoal;
use Cai\Care\Models\CarePlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

final class CareGoalResource extends Resource
{
    protected static ?string $model = CareGoal::class;

    protected static ?string $slug = 'care-goals';

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationLabel = 'Objetivos de atención';

    protected static ?string $navigationGroup = 'Atención';

    protected static ?int $navigationSort = 20;

    protected static bool $isGloballySearchable = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('care_plan_id')
                ->label('Plan')
                ->options(CarePlan::query()->pluck('title', 'id')->all())
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('title')->label('Objetivo')->required()->maxLength(200),
            Forms\Components\Textarea::make('description')->label('Descripción')->columnSpanFull(),
            Forms\Components\Textarea::make('baseline')->label('Línea base')->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options(['pending' => 'Pendiente', 'in_progress' => 'En curso', 'achieved' => 'Logrado', 'dropped' => 'Descartado', 'postponed' => 'Postergado'])
                ->default('pending'),
            Forms\Components\DatePicker::make('target_date')->label('Fecha objetivo'),
            Forms\Components\DatePicker::make('review_date')->label('Revisión'),
            Forms\Components\Textarea::make('progress_notes')->label('Progreso')->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')->label('Orden')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.title')->label('Plan')->searchable(),
                Tables\Columns\TextColumn::make('title')->label('Objetivo')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge(),
                Tables\Columns\TextColumn::make('target_date')->label('Objetivo')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('review_date')->label('Revisión')->date('d/m/Y'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCareGoals::route('/'),
            'create' => Pages\CreateCareGoal::route('/create'),
            'edit' => Pages\EditCareGoal::route('/{record}/edit'),
        ];
    }
}
