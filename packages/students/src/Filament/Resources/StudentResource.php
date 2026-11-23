<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources;

use Cai\Identity\Models\User;
use Cai\Students\Filament\Resources\StudentResource\Pages;
use Cai\Students\Models\Guardian;
use Cai\Students\Models\Student;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ficha del alumno (08.05 §2.5). Las pestañas de Matrículas, Sesiones,
 * Atención, Documentos y Cobranza llegan con F4, F4, F5, F7 y F6: no se crean
 * vacías en F3. El listado aplica el scope de visibilidad (04.03 §3).
 */
final class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $slug = 'students';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Alumnos';

    protected static ?string $modelLabel = 'alumno';

    protected static ?string $pluralModelLabel = 'alumnos';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Ficha')->tabs([
                Tabs\Tab::make('Datos')->schema([
                    Forms\Components\TextInput::make('student_code')
                        ->label('Código')
                        ->disabled()
                        ->placeholder('Se genera al guardar'),
                    Forms\Components\TextInput::make('first_name')->label('Nombre')->required()->maxLength(100),
                    Forms\Components\TextInput::make('last_name')->label('Apellido')->required()->maxLength(100),
                    Forms\Components\DatePicker::make('birth_date')->label('Fecha de nacimiento')->native(false),
                    Forms\Components\Select::make('gender')
                        ->label('Género')
                        ->options(['masculino' => 'Masculino', 'femenino' => 'Femenino', 'otro' => 'Otro'])
                        ->nullable(),
                    Forms\Components\TextInput::make('document_id')->label('Documento')->maxLength(30),
                    Forms\Components\TextInput::make('address')->label('Dirección')->maxLength(255),
                    Forms\Components\TextInput::make('district')->label('Barrio')->maxLength(80),
                    Forms\Components\TextInput::make('city')->label('Ciudad')->maxLength(80),
                    Forms\Components\TextInput::make('phone')->label('Teléfono')->maxLength(40),
                    Forms\Components\TextInput::make('email')->label('Email')->email()->maxLength(254),
                    Forms\Components\DatePicker::make('admission_date')->label('Fecha de ingreso')->native(false),
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options(['active' => 'Activo', 'inactive' => 'Inactivo', 'withdrawn' => 'Retirado'])
                        ->required()
                        ->default('active'),
                    Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
                ]),
                Tabs\Tab::make('Tutores')->schema([
                    Forms\Components\Select::make('guardians')
                        ->label('Tutores')
                        ->relationship(
                            'guardians',
                            'first_name',
                            modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('last_name'),
                        )
                        ->getOptionLabelFromRecordUsing(
                            fn (Guardian $record): string => $record->fullName().' — '.($record->document_id ?? 'sin documento'),
                        )
                        ->searchable(['first_name', 'last_name', 'document_id'])
                        ->preload()
                        ->multiple()
                        ->pivotData(fn (): array => [
                            'relationship' => 'Tutor',
                            'is_primary' => false,
                            'can_pick_up' => false,
                            'receives_admin_messages' => false,
                            'is_emergency_contact' => false,
                            'valid_from' => now()->toDateString(),
                        ])
                        ->helperText('Un alumno puede tener varios tutores. La restricción de tutor principal se mantiene en la base (08.05 §2.3); esta pantalla no la modifica.'),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student_code')->label('Código')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('first_name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')->label('Apellido')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('document_id')->label('Documento')->searchable(),
                Tables\Columns\TextColumn::make('birth_date')->label('Nacimiento')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Activo', 'inactive' => 'Inactivo', 'withdrawn' => 'Retirado',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('last_name');
    }

    /**
     * Segunda barrera (04.03 §3): la consulta devuelve solo los alumnos
     * visibles para el usuario, también para los selectores que la reutilicen.
     */
    public static function getEloquentQuery(): Builder
    {
        $authUser = auth()->user();
        $user = $authUser instanceof User ? $authUser : null;

        // Student::query() mantiene el tipado de Builder<Student> que Larastan
        // necesita para reconocer el scope visibleTo (04.03 §3).
        return Student::query()
            ->visibleTo($user)
            ->with(['guardians', 'familyAccountMemberships']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
