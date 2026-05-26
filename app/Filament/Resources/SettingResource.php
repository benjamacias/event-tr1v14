<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\HtmlString;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'Configuracion';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('key')->label('Clave')->required()->maxLength(255),
            Select::make('type')->options([
                'text' => 'Texto',
                'boolean' => 'Booleano',
                'image' => 'Imagen',
            ])->required()->reactive(),
            Textarea::make('value_text')
                ->label('Valor')
                ->rows(5)
                ->afterStateHydrated(fn (Textarea $component, ?Setting $record): mixed => $component->state($record?->value))
                ->columnSpanFull()
                ->hidden(fn (callable $get): bool => $get('type') === 'image'),
            FileUpload::make('value_file')
                ->label('Imagen')
                ->disk('public')
                ->directory('logos')
                ->image()
                ->enableOpen()
                ->enableDownload()
                ->afterStateHydrated(fn (FileUpload $component, ?Setting $record): mixed => $component->state($record?->value ? [$record->value] : []))
                ->helperText('El archivo se guarda en storage/app/public/logos y el valor queda como ruta relativa.')
                ->visible(fn (callable $get): bool => $get('type') === 'image')
                ->columnSpanFull(),
            Placeholder::make('image_preview')
                ->label('Archivo cargado')
                ->content(function (?Setting $record): HtmlString {
                    $path = $record?->value;

                    if (! $path) {
                        return new HtmlString('<span class="text-gray-500">Sin imagen cargada.</span>');
                    }

                    $filename = e(basename($path));
                    $url = e(asset('storage/'.$path));
                    $pathLabel = e($path);

                    return new HtmlString(<<<HTML
<div class="space-y-3">
    <div class="text-sm text-gray-600">
        <strong>Archivo:</strong> {$filename}<br>
        <strong>Ruta:</strong> {$pathLabel}
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4">
        <img src="{$url}" alt="{$filename}" style="max-height: 180px; max-width: 100%; object-fit: contain;">
    </div>
</div>
HTML);
                })
                ->visible(fn (callable $get): bool => $get('type') === 'image')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label('Clave')->searchable(),
                TextColumn::make('type')->label('Tipo'),
                TextColumn::make('value')->label('Valor')->limit(60),
                TextColumn::make('updated_at')->label('Actualizado')->dateTime(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
