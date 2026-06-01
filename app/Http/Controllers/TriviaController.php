<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttemptAnswerRequest;
use App\Models\AnswerOption;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Services\PlayableQuestionSetPicker;
use App\Services\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TriviaController extends Controller
{
    public function show(Request $request, Attempt $attempt): View|RedirectResponse
    {
        $attempt->load(['questionSet', 'answers.answerOption', 'answers.question']);

        $feedback = null;
        if ($request->filled('feedback')) {
            $feedback = $attempt->answers()
                ->with(['answerOption', 'question.answerOptions'])
                ->find($request->integer('feedback'));
        }

        if ($attempt->status === Attempt::STATUS_COMPLETED && ! $feedback) {
            return redirect()->route('play.result', $attempt);
        }

        return view('trivia.show', [
            'attempt' => $attempt,
            'question' => $feedback?->question ?? $attempt->nextQuestion(),
            'feedback' => $feedback,
            'answeredCount' => $attempt->answers()->count(),
        ]);
    }

    public function answer(StoreAttemptAnswerRequest $request, Attempt $attempt): RedirectResponse
    {
        if ($attempt->status === Attempt::STATUS_COMPLETED) {
            return redirect()->route('play.result', $attempt);
        }

        $attempt->load('questionSet');

        $nextQuestion = $attempt->nextQuestion();
        if (! $nextQuestion || $nextQuestion->id !== $request->integer('question_id')) {
            return redirect()->route('play.show', $attempt);
        }

        $question = Question::query()
            ->where('question_set_id', $attempt->question_set_id)
            ->where('is_active', true)
            ->findOrFail($request->integer('question_id'));

        $option = AnswerOption::query()
            ->where('question_id', $question->id)
            ->findOrFail($request->integer('answer_option_id'));

        if ($attempt->answers()->where('question_id', $question->id)->exists()) {
            return redirect()->route('play.show', $attempt);
        }

        $answer = DB::transaction(function () use ($attempt, $question, $option): AttemptAnswer {
            $answer = AttemptAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'answer_option_id' => $option->id,
                'is_correct' => $option->is_correct,
                'answered_at' => now(),
            ]);

            $answeredCount = $attempt->answers()->count();
            $correctCount = $attempt->answers()->where('is_correct', true)->count();

            if ($answeredCount >= 5) {
                $attempt->update([
                    'status' => Attempt::STATUS_COMPLETED,
                    'completed_at' => now(),
                    'correct_answers_count' => $correctCount,
                    'total_time_seconds' => $correctCount === 5 && $attempt->started_at
                        ? $attempt->started_at->diffInSeconds(now())
                        : null,
                ]);
            } else {
                $attempt->update(['correct_answers_count' => $correctCount]);
            }

            return $answer;
        });

        if ($attempt->fresh()->status === Attempt::STATUS_COMPLETED) {
            return redirect()->route('play.show', [$attempt, 'feedback' => $answer->id]);
        }

        return redirect()->route('play.show', [$attempt, 'feedback' => $answer->id]);
    }

    public function result(Attempt $attempt, Settings $settings): View
    {
        $attempt->load(['participant', 'questionSet']);

        return view('trivia.result', [
            'attempt' => $attempt,
            'partialMessage' => $settings->get('final_message_partial', 'Gracias por participar! Respondiste :score/5 preguntas correctamente!'),
            'perfectMessage' => $settings->get('final_message_perfect', 'Felicitaciones!! Respondiste todo perfecto! Tu tiempo final fue de :time. Estás participando por el premio final!'),
        ]);
    }

    public function close(Request $request, Attempt $attempt, PlayableQuestionSetPicker $picker): View
    {
        $attempt->load('participant');

        $playedSetIds = collect([(int) $attempt->question_set_id]);
        $deviceIdentifier = $attempt->device_identifier ?: $request->cookie('ianus_device_id');

        if (filled($attempt->participant->document_number)) {
            $playedSetIds = $playedSetIds->merge(
                Attempt::query()
                    ->whereHas('participant', fn ($participantQuery) => $participantQuery
                        ->where('document_number', $attempt->participant->document_number))
                    ->pluck('question_set_id')
                    ->map(fn ($id) => (int) $id)
            );
        }

        if (filled($deviceIdentifier)) {
            $playedSetIds = $playedSetIds->merge(
                Attempt::query()
                    ->where('device_identifier', $deviceIdentifier)
                    ->pluck('question_set_id')
                    ->map(fn ($id) => (int) $id)
            );
        }

        $cookiePlayedSetIds = collect(json_decode($request->cookie('ianus_played_sets', '[]'), true) ?: [])
            ->filter()
            ->map(fn ($id) => (int) $id);

        $nextQuestionSet = $picker->pick(
            $playedSetIds->merge($cookiePlayedSetIds)->unique()->values()->all()
        );

        return view('trivia.close', [
            'canPlayAgain' => $nextQuestionSet !== null,
        ]);
    }
}
