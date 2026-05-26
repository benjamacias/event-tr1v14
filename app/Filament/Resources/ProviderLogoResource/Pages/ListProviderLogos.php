<?php

namespace App\Filament\Resources\ProviderLogoResource\Pages;

use App\Filament\Resources\ProviderLogoResource;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProviderLogos extends ListRecords
{
    protected static string $resource = ProviderLogoResource::class;

    protected function getActions(): array
    {
        return [CreateAction::make()];
    }
}
