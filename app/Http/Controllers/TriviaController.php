<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttemptAnswerRequest;
use App\Models\AnswerOption;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Services\PlayableQuestionSetPicker;
use App\Services\Settings;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
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

    public function result(Request $request, Attempt $attempt, Settings $settings, PlayableQuestionSetPicker $picker): View
    {
        $attempt->load(['participant', 'questionSet']);
        $nextQuestionSet = $this->nextQuestionSet($request, $attempt, $picker);

        if ($attempt->status === Attempt::STATUS_COMPLETED && ! $nextQuestionSet) {
            return view('trivia.finished');
        }

        return view('trivia.result', [
            'attempt' => $attempt,
            'canPlayAgain' => $nextQuestionSet !== null,
            'partialMessage' => $settings->get('final_message_partial', 'Gracias por participar! Respondiste :score/5 preguntas correctamente!'),
            'perfectMessage' => $settings->get('final_message_perfect', 'Felicitaciones!! Respondiste todo perfecto! Tu tiempo final fue de :time. Estás participando por el premio final!'),
        ]);
    }

    public function next(Request $request, Attempt $attempt, PlayableQuestionSetPicker $picker): RedirectResponse|View
    {
        $attempt->load('participant');

        if ($attempt->status !== Attempt::STATUS_COMPLETED) {
            return redirect()->route('play.show', $attempt);
        }

        $existingNextAttempt = Attempt::query()
            ->where('participant_id', $attempt->participant_id)
            ->where('id', '>', $attempt->id)
            ->orderBy('id')
            ->first();

        if ($existingNextAttempt) {
            return redirect()->route('play.show', $existingNextAttempt);
        }

        $playedSetIds = $this->playedSetIds($request, $attempt);
        $nextQuestionSet = $picker->pick($playedSetIds->all());

        if (! $nextQuestionSet) {
            return view('trivia.finished');
        }

        try {
            $nextAttempt = DB::transaction(function () use ($request, $attempt, $nextQuestionSet): Attempt {
                $existingAttempt = Attempt::query()
                    ->where('participant_id', $attempt->participant_id)
                    ->where('question_set_id', $nextQuestionSet->id)
                    ->first();

                if ($existingAttempt) {
                    return $existingAttempt;
                }

                return Attempt::create([
                    'participant_id' => $attempt->participant_id,
                    'question_set_id' => $nextQuestionSet->id,
                    'status' => Attempt::STATUS_STARTED,
                    'started_at' => now(),
                    'duplicate_flag' => false,
                    'device_identifier' => $attempt->device_identifier ?: $request->cookie('ianus_device_id'),
                    'user_agent' => $attempt->user_agent,
                    'ip_address' => $attempt->ip_address,
                ]);
            });
        } catch (QueryException $exception) {
            $nextAttempt = Attempt::query()
                ->where('participant_id', $attempt->participant_id)
                ->where('question_set_id', $nextQuestionSet->id)
                ->first();

            if (! $nextAttempt) {
                throw $exception;
            }
        }

        $deviceIdentifier = $attempt->device_identifier ?: $request->cookie('ianus_device_id');
        $playedSetIds->push($nextQuestionSet->id);

        $redirect = redirect()
            ->route('play.show', $nextAttempt)
            ->cookie('ianus_played_sets', $playedSetIds->unique()->values()->toJson(), 60 * 24 * 30);

        if (filled($deviceIdentifier)) {
            $redirect->cookie('ianus_device_id', $deviceIdentifier, 60 * 24 * 365);
        }

        return $redirect;
    }

    public function close(Request $request, Attempt $attempt, PlayableQuestionSetPicker $picker): View
    {
        $canPlayAgain = $this->canPlayAgain($request, $attempt, $picker);

        if (! $canPlayAgain) {
            return view('trivia.finished');
        }

        return view('trivia.close', [
            'attempt' => $attempt,
            'canPlayAgain' => $canPlayAgain,
        ]);
    }

    private function canPlayAgain(Request $request, Attempt $attempt, PlayableQuestionSetPicker $picker): bool
    {
        return $this->nextQuestionSet($request, $attempt, $picker) !== null;
    }

    private function nextQuestionSet(Request $request, Attempt $attempt, PlayableQuestionSetPicker $picker): ?QuestionSet
    {
        return $picker->pick($this->playedSetIds($request, $attempt)->all());
    }

    private function playedSetIds(Request $request, Attempt $attempt): Collection
    {
        $attempt->load('participant');

        $playedSetIds = collect([(int) $attempt->question_set_id]);
        $deviceIdentifier = $attempt->device_identifier ?: $request->cookie('ianus_device_id');

        if ($attempt->participant) {
            $playedSetIds = $playedSetIds->merge(
                Attempt::query()
                    ->where('participant_id', $attempt->participant_id)
                    ->pluck('question_set_id')
                    ->map(fn ($id) => (int) $id)
            );
        }

        if ($attempt->participant && (
            filled($attempt->participant->document_number)
            || filled($attempt->participant->email)
            || filled($attempt->participant->phone)
        )) {
            $playedSetIds = $playedSetIds->merge(
                Attempt::query()
                    ->whereHas('participant', fn ($participantQuery) => $participantQuery
                        ->where(fn ($identityQuery) => $identityQuery
                            ->when(filled($attempt->participant->document_number), fn ($query) => $query
                                ->orWhere('document_number', $attempt->participant->document_number))
                            ->when(filled($attempt->participant->email), fn ($query) => $query
                                ->orWhereRaw('LOWER(email) = ?', [mb_strtolower($attempt->participant->email)]))
                            ->when(filled($attempt->participant->phone), fn ($query) => $query
                                ->orWhere('phone', $attempt->participant->phone))))
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

        return $playedSetIds->merge($cookiePlayedSetIds)->unique()->values();
    }
}
