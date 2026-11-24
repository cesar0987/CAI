<?php

declare(strict_types=1);

namespace Cai\Care\Filament\Resources;

use Cai\Care\Filament\Resources\CarePlanResource\Pages;
use Cai\Care\Models\CarePlan;
use Cai\Scheduling\Models\Professional;
use Cai\Students\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

final class CarePlanResource extends Resource
{
    protected static ?string $model = CarePlan::class;

    protected static ?string $slug = 'care-plans';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Planes de atención';

    protected static ?string $navigationGroup = 'Atención';

    protected static ?int $navigationSort = 10;

    protected static bool $isGloballySearchable = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->label('Alumno')
                ->options(Student::query()->pluck('student_code', 'id')->all())
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('title')->label('Título')->required()->maxLength(200),
            Forms\Components\Textarea::make('diagnosis')->label('Diagnóstico')->columnSpanFull(),
            Forms\Components\DatePicker::make('start_date')->label('Inicio')->required(),
            Forms\Components\DatePicker::make('end_date')->label('Fin'),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options(['draft' => 'Borrador', 'active' => 'Activo', 'under_review' => 'En revisión', 'closed' => 'Cerrado'])
                ->default('draft'),
            Forms\Components\Select::make('responsible_professional_id')
                ->label('Profesional responsable')
                ->options(Professional::query()->pluck('display_name', 'id')->all())
                ->searchable(),
            Forms\Components\Textarea::make('goals_summary')->label('Resumen de objetivos')->columnSpanFull(),
            Forms\Components\TextInput::make('review_interval_days')->label('Revisión (días)')->numeric()->default(90),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.student_code')->label('Código')->searchable(),
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge(),
                Tables\Columns\TextColumn::make('start_date')->label('Inicio')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('end_date')->label('Fin')->date('d/m/Y'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarePlans::route('/'),
            'create' => Pages\CreateCarePlan::route('/create'),
            'edit' => Pages\EditCarePlan::route('/{record}/edit'),
        ];
    }
}
