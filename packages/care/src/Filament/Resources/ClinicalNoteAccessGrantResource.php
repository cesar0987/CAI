<?php

declare(strict_types=1);

namespace Cai\Care\Filament\Resources;

use Cai\Care\Actions\RevokeClinicalNoteGrant;
use Cai\Care\Data\RevokeClinicalNoteGrantData;
use Cai\Care\Filament\Resources\ClinicalNoteAccessGrantResource\Pages;
use Cai\Care\Models\ClinicalNoteAccessGrant;
use Cai\Identity\Models\User;
use Cai\Students\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/** Solo Dirección y Coordinación (según care.grants.* en 04.02). */
final class ClinicalNoteAccessGrantResource extends Resource
{
    protected static ?string $model = ClinicalNoteAccessGrant::class;

    protected static ?string $slug = 'clinical-grants';

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Grants clínicos';

    protected static ?string $navigationGroup = 'Atención';

    protected static ?int $navigationSort = 40;

    protected static bool $isGloballySearchable = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->label('Alumno')
                ->options(Student::query()->pluck('student_code', 'id')->all())
                ->searchable()
                ->required(),
            Forms\Components\Select::make('granted_to_user_id')
                ->label('Usuario autorizado')
                ->options(User::query()->pluck('name', 'id')->all())
                ->searchable()
                ->required(),
            Forms\Components\Textarea::make('reason')->label('Motivo (mínimo 20 caracteres)')->required()->minLength(20)->columnSpanFull(),
            Forms\Components\TextInput::make('days')->label('Días de vigencia')->numeric()->default(7)->minValue(1)->maxValue(30),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.student_code')->label('Alumno'),
                Tables\Columns\TextColumn::make('grantedTo.name')->label('Autorizado'),
                Tables\Columns\TextColumn::make('reason')->label('Motivo')->limit(80)->wrap(),
                Tables\Columns\TextColumn::make('expires_at')->label('Vence')->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('revoked_at')->label('Revocado')->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('created_at')->label('Emitido')->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Tables\Actions\Action::make('revoke')
                    ->label('Revocar')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (ClinicalNoteAccessGrant $record): bool => $record->isActive())
                    ->form([
                        Forms\Components\Textarea::make('reason')->label('Motivo de la revocación')->required(),
                    ])
                    ->action(function (ClinicalNoteAccessGrant $record, array $data): void {
                        Gate::authorize('revoke', $record);
                        app(RevokeClinicalNoteGrant::class)->execute(new RevokeClinicalNoteGrantData(
                            $record->id,
                            Auth::id(),
                            $data['reason'],
                        ));
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClinicalNoteAccessGrants::route('/'),
            'create' => Pages\CreateClinicalNoteAccessGrant::route('/create'),
        ];
    }
}
