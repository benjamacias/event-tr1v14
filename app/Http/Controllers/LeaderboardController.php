<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use Illuminate\Http\JsonResponse;

class LeaderboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $attempts = Attempt::query()
            ->with(['participant:id,full_name', 'questionSet:id,name'])
            ->where('status', Attempt::STATUS_COMPLETED)
            ->whereIn('correct_answers_count', [3, 4, 5])
            ->get()
            ->sortBy(fn (Attempt $attempt) => match ($attempt->correct_answers_count) {
                5 => '0-'.str_pad((string) ($attempt->total_time_seconds ?? 999999), 6, '0', STR_PAD_LEFT),
                4 => '1-'.$attempt->id,
                default => '2-'.$attempt->id,
            })
            ->values()
            ->map(fn (Attempt $attempt) => [
                'name' => $attempt->participant->full_name,
                'score' => $attempt->correct_answers_count.'/5',
                'set' => $attempt->questionSet->name,
                'time_seconds' => $attempt->correct_answers_count === 5 ? $attempt->total_time_seconds : null,
                'time' => $attempt->correct_answers_count === 5 ? $attempt->formattedTime() : null,
            ]);

        return response()->json(['data' => $attempts]);
    }
}
