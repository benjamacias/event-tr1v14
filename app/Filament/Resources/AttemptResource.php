<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttemptResource\Pages;
use App\Models\Attempt;
use App\Models\QuestionSet;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class AttemptResource extends Resource
{
    protected static ?string $model = Attempt::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Intentos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('status')->options(['started' => 'Started', 'completed' => 'Completed'])->required(),
            TextInput::make('correct_answers_count')->label('Correctas')->numeric(),
            TextInput::make('total_time_seconds')->label('Tiempo segundos')->numeric(),
            Toggle::make('duplicate_flag')->label('Posible duplicado'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('participant.full_name')->label('Participante')->searchable(),
                TextColumn::make('questionSet.name')->label('Set')->sortable(),
                TextColumn::make('status')->label('Estado'),
                TextColumn::make('started_at')->label('Inicio')->dateTime(),
                TextColumn::make('completed_at')->label('Fin')->dateTime(),
                TextColumn::make('correct_answers_count')->label('Correctas')->sortable(),
                TextColumn::make('total_time_seconds')->label('Tiempo')->sortable(),
                IconColumn::make('duplicate_flag')->label('Duplicado')->boolean(),
            ])
            ->filters([
                SelectFilter::make('question_set_id')->label('Set')->options(fn (): array => QuestionSet::query()->orderBy('id')->pluck('name', 'id')->all()),
                SelectFilter::make('correct_answers_count')->label('Correctas')->options([0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttempts::route('/'),
            'edit' => Pages\EditAttempt::route('/{record}/edit'),
        ];
    }
}
