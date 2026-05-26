<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use App\Models\QuestionSet;
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

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'Preguntas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('question_set_id')->label('Set')->options(fn () => QuestionSet::query()->pluck('name', 'id'))->required()->searchable(),
            Textarea::make('text')->label('Pregunta')->required()->rows(4)->columnSpanFull(),
            Textarea::make('explanation')->label('Aclaracion general')->rows(3)->columnSpanFull(),
            TextInput::make('sort_order')->label('Orden')->numeric()->default(1)->required(),
            Toggle::make('is_active')->label('Activa')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('questionSet.name')->label('Set')->sortable(),
                TextColumn::make('text')->label('Pregunta')->limit(60)->searchable(),
                TextColumn::make('sort_order')->label('Orden')->sortable(),
                IconColumn::make('is_active')->label('Activa')->boolean(),
            ])
            ->filters([SelectFilter::make('question_set_id')->label('Set')->relationship('questionSet', 'name')])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
