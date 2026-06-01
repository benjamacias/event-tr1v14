<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionSetResource\Pages;
use App\Models\QuestionSet;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class QuestionSetResource extends Resource
{
    protected static ?string $model = QuestionSet::class;
    protected static ?string $navigationIcon = 'heroicon-o-view-list';
    protected static ?string $navigationLabel = 'Sets de preguntas';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Datos del set')
                ->schema([
                    TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                    TextInput::make('slug')->label('Slug')->unique(ignoreRecord: true)->maxLength(255),
                    TextInput::make('sort_order')
                        ->label('Orden de juego')
                        ->numeric()
                        ->helperText('Menor numero juega primero. Si dos sets tienen el mismo orden o esta vacio, se usa el ID como desempate.'),
                    Toggle::make('is_active')->label('Activo')->default(true),
                    Toggle::make('show_correct_answer_on_error')
                        ->label('Mostrar respuesta correcta al equivocarse')
                        ->helperText('Si esta activo, el participante vera la letra y el texto completo de la respuesta correcta cuando falle.')
                        ->default(true),
                ])
                ->columns(2),

            Section::make('Preguntas y respuestas')
                ->schema([
                    Repeater::make('questions')
                        ->label('Preguntas')
                        ->relationship('questions')
                        ->orderable('sort_order')
                        ->reorderableWithButtons()
                        ->defaultItems(5)
                        ->minItems(5)
                        ->maxItems(5)
                        ->createItemButtonLabel('Agregar pregunta')
                        ->itemLabel(fn (array $state): ?string => filled($state['text'] ?? null)
                            ? (string) str($state['text'])->limit(60)
                            : 'Pregunta')
                        ->schema([
                            Textarea::make('text')
                                ->label('Pregunta')
                                ->required()
                                ->rows(3)
                                ->columnSpanFull(),
                            Textarea::make('explanation')
                                ->label('Aclaracion general')
                                ->rows(2)
                                ->columnSpanFull(),
                            Toggle::make('is_active')
                                ->label('Activa')
                                ->default(true),
                            Repeater::make('answerOptions')
                                ->label('Respuestas')
                                ->relationship('answerOptions')
                                ->orderable('sort_order')
                                ->reorderableWithButtons()
                                ->defaultItems(3)
                                ->minItems(3)
                                ->maxItems(3)
                                ->createItemButtonLabel('Agregar respuesta')
                                ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::normalizeAnswerOptionData($data))
                                ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => self::normalizeAnswerOptionData($data))
                                ->itemLabel(fn (array $state): ?string => filled($state['label'] ?? null)
                                    ? 'Respuesta '.$state['label']
                                    : 'Respuesta')
                                ->schema([
                                    Hidden::make('label')->default('A'),
                                    Toggle::make('is_correct')
                                        ->label('Correcta'),
                                    Textarea::make('text')
                                        ->label('Respuesta')
                                        ->required()
                                        ->rows(2)
                                        ->columnSpanFull(),
                                    Textarea::make('explanation')
                                        ->label('Aclaracion')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    private static function normalizeAnswerOptionData(array $data): array
    {
        $data['label'] = match ((int) ($data['sort_order'] ?? 1)) {
            1 => 'A',
            2 => 'B',
            default => 'C',
        };

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('sort_order')->label('Orden de juego')->sortable(),
                TextColumn::make('questions_count')->counts('questions')->label('Preguntas'),
                IconColumn::make('show_correct_answer_on_error')->label('Muestra correcta')->boolean(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
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
