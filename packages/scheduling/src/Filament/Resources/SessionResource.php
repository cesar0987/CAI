<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Resources;

use Cai\Identity\Models\User;
use Cai\Scheduling\Actions\RecordAttendance;
use Cai\Scheduling\Data\RecordAttendanceData;
use Cai\Scheduling\Filament\Resources\SessionResource\Pages;
use Cai\Scheduling\Models\Session;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Agenda operativa (08.06 §2.7). El listado y el acceso directo respetan el
 * scope de SessionPolicy / Session::visibleTo (E-08).
 */
final class SessionResource extends Resource
{
    protected static ?string $model = Session::class;

    protected static ?string $slug = 'sessions';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Sesiones';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('service_id')->label('Servicio')->relationship('service', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('group_id')->label('Grupo')->relationship('group', 'name')->searchable()->preload()->nullable(),
            Forms\Components\Select::make('session_type')
                ->label('Tipo')
                ->options(['individual' => 'Individual', 'group' => 'Grupal'])
                ->required()
                ->default('individual'),
            Forms\Components\DateTimePicker::make('starts_at')->label('Inicio')->required()->native(false),
            Forms\Components\DateTimePicker::make('ends_at')->label('Fin')->required()->native(false),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options([
                    'scheduled' => 'Programada', 'completed' => 'Completada',
                    'cancelled' => 'Cancelada', 'rescheduled' => 'Reprogramada',
                ])
                ->required()
                ->default('scheduled'),
            Forms\Components\TextInput::make('location')->label('Sala / lugar')->maxLength(150),
            Forms\Components\Textarea::make('notes')->label('Notas operativas')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('starts_at')->label('Inicio')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->label('Fin')->dateTime('H:i'),
                Tables\Columns\TextColumn::make('service.name')->label('Servicio')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('group.name')->label('Grupo')->placeholder('Individual'),
                Tables\Columns\TextColumn::make('session_type')->label('Tipo')->badge(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'scheduled' => 'Programada', 'completed' => 'Completada',
                    'cancelled' => 'Cancelada', 'rescheduled' => 'Reprogramada',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('recordAttendance')
                    ->label('Registrar asistencia')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (Session $record): bool => in_array($record->status, ['scheduled', 'completed'], true))
                    ->form([
                        Forms\Components\Repeater::make('records')
                            ->label('Asistencia por participante')
                            ->schema([
                                Forms\Components\Hidden::make('participant_student_id'),
                                Forms\Components\TextInput::make('student')->label('Alumno')->disabled(),
                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'present' => 'Presente', 'absent' => 'Ausente',
                                        'late' => 'Tardío', 'excused' => 'Autorizado',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('justification')->label('Justificación'),
                            ])
                            ->columns(4)
                            ->default(function (Session $record): array {
                                return $record->participants
                                    ->map(fn ($participant): array => [
                                        'participant_student_id' => $participant->student_id,
                                        'student' => $participant->student?->fullName() ?? (string) $participant->student_id,
                                    ])
                                    ->all();
                            }),
                    ])
                    ->action(function (Session $record, array $data): void {
                        $records = array_values(array_filter(
                            $data['records'] ?? [],
                            static fn (array $row): bool => filled($row['status'] ?? null),
                        ));

                        app(RecordAttendance::class)->execute(new RecordAttendanceData(
                            $record->id,
                            $records,
                            (int) auth()->id(),
                        ));

                        Notification::make()->title('Asistencia registrada')->success()->send();
                    }),
            ])
            ->defaultSort('starts_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $authUser = auth()->user();
        $user = $authUser instanceof User ? $authUser : null;

        return Session::query()->visibleTo($user)->with(['service', 'group', 'professionals']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSessions::route('/'),
            'create' => Pages\CreateSession::route('/create'),
            'edit' => Pages\EditSession::route('/{record}/edit'),
        ];
    }
}
