<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources;

use Cai\Identity\Models\User;
use Cai\Scheduling\Filament\Resources\EnrollmentResource\Pages;
use Cai\Scheduling\Models\Enrollment;
use Cai\Students\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $slug = 'enrollments';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Inscripciones';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->label('Alumno')
                ->relationship(
                    'student',
                    'student_code',
                    modifyQueryUsing: function (Builder $query): Builder {
                        $authUser = auth()->user();
                        $user = $authUser instanceof User ? $authUser : null;

                        return $query->whereIn('id', Student::query()->visibleTo($user)->select('id'));
                    },
                )
                ->getOptionLabelFromRecordUsing(fn (Student $record): string => $record->student_code.' — '.$record->fullName())
                ->searchable(['student_code', 'first_name', 'last_name'])
                ->preload()
                ->required(),
            Forms\Components\Select::make('service_id')
                ->label('Servicio')
                ->relationship('service', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\DatePicker::make('start_date')->label('Desde')->required()->native(false),
            Forms\Components\DatePicker::make('end_date')->label('Hasta')->native(false),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options(['active' => 'Activa', 'paused' => 'Pausada', 'completed' => 'Completada', 'cancelled' => 'Cancelada'])
                ->required()
                ->default('active'),
            Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.student_code')->label('Alumno')->searchable(),
                Tables\Columns\TextColumn::make('service.name')->label('Servicio')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('start_date')->label('Desde')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('end_date')->label('Hasta')->date('d/m/Y')->placeholder('Abierta'),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('start_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}
