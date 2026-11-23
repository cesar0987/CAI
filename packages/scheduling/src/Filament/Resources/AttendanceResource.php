<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources;

use Cai\Scheduling\Filament\Resources\AttendanceResource\Pages;
use Cai\Scheduling\Models\AttendanceRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * La asistencia se registra desde la sesión (Action "Registrar asistencia")
 * llamando a RecordAttendance: este recurso es de consulta y no crea filas
 * directamente, para que toda corrección quede auditada.
 */
final class AttendanceResource extends Resource
{
    protected static ?string $model = AttendanceRecord::class;

    protected static ?string $slug = 'attendance';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Asistencia';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 60;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('participant.student.student_code')->label('Alumno')->disabled(),
            Forms\Components\TextInput::make('status')->label('Estado')->disabled(),
            Forms\Components\DateTimePicker::make('recorded_at')->label('Registrado')->disabled(),
            Forms\Components\Textarea::make('notes')->label('Justificación / notas')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('participant.student.student_code')->label('Alumno')->searchable(),
                Tables\Columns\TextColumn::make('participant.session.starts_at')->label('Sesión')->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()->sortable(),
                Tables\Columns\TextColumn::make('recordedBy.name')->label('Registrado por'),
                Tables\Columns\TextColumn::make('recorded_at')->label('Fecha')->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('recorded_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendance::route('/'),
        ];
    }
}
