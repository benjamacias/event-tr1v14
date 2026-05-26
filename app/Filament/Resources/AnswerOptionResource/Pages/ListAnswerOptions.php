<?php

namespace App\Filament\Resources\AnswerOptionResource\Pages;

use App\Filament\Resources\AnswerOptionResource;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAnswerOptions extends ListRecords
{
    protected static string $resource = AnswerOptionResource::class;

    protected function getActions(): array
    {
        return [CreateAction::make()];
    }
}
