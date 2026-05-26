<?php

namespace App\Filament\Resources\QuestionSetResource\Pages;

use App\Filament\Resources\QuestionSetResource;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuestionSets extends ListRecords
{
    protected static string $resource = QuestionSetResource::class;

    protected function getActions(): array
    {
        return [CreateAction::make()];
    }
}
