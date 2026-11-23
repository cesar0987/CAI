<?php

declare(strict_types=1);

namespace Cai\Students\Filament\Resources;

use Cai\Identity\Models\User;
use Cai\Students\Filament\Resources\AuthorizedPickupResource\Pages;
use Cai\Students\Models\AuthorizedPickup;
use Cai\Students\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Terceros autorizados a retirar (03.02 §7). El selector de alumnos aplica el
 * mismo scope que el listado; una autorización se revoca con `is_active`.
 */
final class AuthorizedPickupResource extends Resource
{
    protected static ?string $model = AuthorizedPickup::class;

    protected static ?string $slug = 'authorized-pickups';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Retiros autorizados';

    protected static ?string $modelLabel = 'persona autorizada';

    protected static ?string $pluralModelLabel = 'personas autorizadas';

    protected static ?int $navigationSort = 40;

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
                ->getOptionLabelFromRecordUsing(
                    fn (Student $record): string => $record->student_code.' — '.$record->fullName(),
                )
                ->searchable(['student_code', 'first_name', 'last_name'])
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('full_name')->label('Nombre completo')->required()->maxLength(150),
            Forms\Components\TextInput::make('document_id')->label('Documento')->required()->maxLength(30),
            Forms\Components\TextInput::make('phone')->label('Teléfono')->maxLength(40),
            Forms\Components\TextInput::make('relationship')->label('Vínculo')->maxLength(80),
            Forms\Components\DatePicker::make('valid_from')->label('Vigente desde')->required()->native(false)->default(now()),
            Forms\Components\DatePicker::make('valid_until')->label('Vigente hasta')->native(false),
            Forms\Components\Toggle::make('is_active')->label('Habilitada')->default(true)
                ->helperText('Una autorización vencida o inactiva NO habilita el retiro.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.student_code')->label('Alumno')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('full_name')->label('Persona')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('document_id')->label('Documento')->searchable(),
                Tables\Columns\TextColumn::make('valid_from')->label('Desde')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('valid_until')->label('Hasta')->date('d/m/Y')->placeholder('Sin vencimiento'),
                Tables\Columns\IconColumn::make('is_active')->label('Habilitada')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('full_name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthorizedPickups::route('/'),
            'create' => Pages\CreateAuthorizedPickup::route('/create'),
            'edit' => Pages\EditAuthorizedPickup::route('/{record}/edit'),
        ];
    }
}
