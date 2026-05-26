<?php

namespace App\Filament\Resources\ParticipantResource\Pages;

use App\Filament\Resources\ParticipantResource;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListParticipants extends ListRecords
{
    protected static string $resource = ParticipantResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Exportar CSV')
                ->url(route('admin.participants.export.csv'))
                ->openUrlInNewTab(),
            Action::make('exportXlsx')
                ->label('Exportar XLSX')
                ->url(route('admin.participants.export.xlsx'))
                ->openUrlInNewTab(),
        ];
    }
}
