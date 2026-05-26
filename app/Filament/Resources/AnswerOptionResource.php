<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnswerOptionResource\Pages;
use App\Models\AnswerOption;
use App\Models\Question;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class AnswerOptionResource extends Resource
{
    protected static ?string $model = AnswerOption::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationLabel = 'Respuestas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('question_id')->label('Pregunta')->options(fn () => Question::query()->pluck('text', 'id'))->required()->searchable(),
            Select::make('label')->label('Letra')->options(['A' => 'A', 'B' => 'B', 'C' => 'C'])->required(),
            Textarea::make('text')->label('Respuesta')->required()->rows(3)->columnSpanFull(),
            Toggle::make('is_correct')->label('Correcta'),
            Textarea::make('explanation')->label('Aclaracion')->rows(3)->columnSpanFull(),
            TextInput::make('sort_order')->label('Orden')->numeric()->default(1)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question.questionSet.name')->label('Set'),
                TextColumn::make('question.text')->label('Pregunta')->limit(40),
                TextColumn::make('label')->label('Letra')->sortable(),
                TextColumn::make('text')->label('Respuesta')->limit(50),
                IconColumn::make('is_correct')->label('Correcta')->boolean(),
            ])
            ->filters([SelectFilter::make('question_id')->label('Pregunta')->relationship('question', 'text')])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnswerOptions::route('/'),
            'create' => Pages\CreateAnswerOption::route('/create'),
            'edit' => Pages\EditAnswerOption::route('/{record}/edit'),
        ];
    }
}
