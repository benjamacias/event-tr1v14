<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionSetResource\Pages;
use App\Models\QuestionSet;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class QuestionSetResource extends Resource
{
    protected static ?string $model = QuestionSet::class;
    protected static ?string $navigationIcon = 'heroicon-o-view-list';
    protected static ?string $navigationLabel = 'Sets de preguntas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->label('Nombre')->required()->maxLength(255),
            TextInput::make('slug')->label('Slug')->unique(ignoreRecord: true)->maxLength(255),
            Toggle::make('is_active')->label('Activo')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('questions_count')->counts('questions')->label('Preguntas'),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->defaultSort('id', 'asc')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestionSets::route('/'),
            'create' => Pages\CreateQuestionSet::route('/create'),
            'edit' => Pages\EditQuestionSet::route('/{record}/edit'),
        ];
    }
}
