<?php

namespace App\Services;

use App\Models\QuestionSet;

class PlayableQuestionSetPicker
{
    public function pick(array $excludedSetIds = []): ?QuestionSet
    {
        $sets = QuestionSet::query()
            ->where('is_active', true)
            ->when($excludedSetIds !== [], fn ($query) => $query->whereNotIn('id', $excludedSetIds))
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with(['questions' => fn ($query) => $query
                ->where('is_active', true)
                ->with('answerOptions')
                ->orderBy('sort_order')])
            ->get()
            ->filter(fn (QuestionSet $set) => $set->isPlayable())
            ->values();

        return $sets->first();
    }
}
