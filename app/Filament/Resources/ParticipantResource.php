<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipantResource\Pages;
use App\Models\Participant;
use App\Models\QuestionSet;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Participantes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('full_name')->label('Nombre y apellido')->required(),
            TextInput::make('email')->label('Mail')->email()->required(),
            TextInput::make('phone')->label('Celular')->required(),
            TextInput::make('institution_role')->label('Institucion/cargo'),
            Toggle::make('consent_accepted')->label('Consentimiento'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Fecha/hora')->dateTime()->sortable(),
                TextColumn::make('full_name')->label('Nombre')->searchable(),
                TextColumn::make('email')->label('Mail')->searchable(),
                TextColumn::make('phone')->label('Celular')->searchable(),
                TextColumn::make('institution_role')->label('Institucion/cargo')->limit(30),
                TextColumn::make('latestAttempt.questionSet.name')->label('Set'),
                TextColumn::make('latestAttempt.correct_answers_count')->label('Correctas'),
                IconColumn::make('consent_accepted')->label('Consentimiento')->boolean(),
            ])
            ->filters([
                SelectFilter::make('question_set_id')
                    ->label('Set')
                    ->options(fn (): array => QuestionSet::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('attempts', fn (Builder $attempts) => $attempts->where('question_set_id', $data['value']))
                        : $query),
                SelectFilter::make('correct_answers_count')
                    ->label('Correctas')
                    ->options([0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('attempts', fn (Builder $attempts) => $attempts->where('correct_answers_count', $data['value']))
                        : $query),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParticipants::route('/'),
            'edit' => Pages\EditParticipant::route('/{record}/edit'),
        ];
    }
}
