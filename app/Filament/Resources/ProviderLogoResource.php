<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderLogoResource\Pages;
use App\Models\ProviderLogo;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class ProviderLogoResource extends Resource
{
    protected static ?string $model = ProviderLogo::class;
    protected static ?string $navigationIcon = 'heroicon-o-photograph';
    protected static ?string $navigationLabel = 'Publicidades';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->label('Nombre de publicidad')->required()->maxLength(255),
            FileUpload::make('image_path')
                ->label('Imagen publicitaria')
                ->disk('public')
                ->directory('logos')
                ->image()
                ->helperText('Recomendado: formato horizontal tipo banner. Se muestra grande en la parte inferior de /screen.'),
            Toggle::make('is_active')->label('Activo')->default(true),
            TextInput::make('sort_order')->label('Orden')->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Publicidad')->searchable(),
                TextColumn::make('sort_order')->label('Orden')->sortable(),
                TextColumn::make('id')->label('Carga')->sortable(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
                TextColumn::make('updated_at')->dateTime(),
            ])
            ->defaultSort('sort_order')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProviderLogos::route('/'),
            'create' => Pages\CreateProviderLogo::route('/create'),
            'edit' => Pages\EditProviderLogo::route('/{record}/edit'),
        ];
    }
}
