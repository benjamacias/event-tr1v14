@extends('layouts.app')

@section('content')
<section class="mx-auto flex min-h-screen w-full max-w-xl flex-col px-5 py-8">
    <div class="mb-5 flex items-center justify-between text-sm text-zinc-300">
        <span>{{ $attempt->questionSet->name }}</span>
        <span>Pregunta {{ min($answeredCount + ($feedback ? 0 : 1), 5) }}/5</span>
    </div>

    @if ($question)
        <article class="flex flex-1 flex-col justify-center">
            <h1 class="text-2xl font-bold leading-tight">{{ $question->text }}</h1>

            @if ($feedback)
                @php
                    $correctOption = $question->answerOptions->firstWhere('is_correct', true);
                    $explanation = $feedback->answerOption->explanation ?: $question->explanation;
                @endphp
                <div class="mt-8 rounded-lg border {{ $feedback->is_correct ? 'border-emerald-400 bg-emerald-500/15' : 'border-red-400 bg-red-500/15' }} p-5">
                    <div class="text-5xl">{{ $feedback->is_correct ? '✓' : '×' }}</div>
                    <p class="mt-3 text-xl font-bold">
                        @if ($feedback->is_correct)
                            Tu respuesta fue correcta
                        @elseif ($attempt->questionSet->show_correct_answer_on_error)
                            Lo sentimos, la respuesta correcta era la {{ $correctOption?->label }}: {{ $correctOption?->text }}
                        @else
                            Lo sentimos, tu respuesta fue incorrecta
                        @endif
                    </p>
                    @if ($explanation)
                        <p class="mt-3 text-zinc-100">{{ $explanation }}</p>
                    @endif
                </div>

                @if ($attempt->fresh()->status === \App\Models\Attempt::STATUS_COMPLETED)
                    <a href="{{ route('play.result', $attempt) }}" class="mt-8 block rounded-lg bg-cyan-300 px-5 py-4 text-center text-lg font-bold text-zinc-950">Ver resultado</a>
                @else
                    <a href="{{ route('play.show', $attempt) }}" class="mt-8 block rounded-lg bg-cyan-300 px-5 py-4 text-center text-lg font-bold text-zinc-950">Siguiente pregunta</a>
                @endif
            @else
                <form method="POST" action="{{ route('play.answer', $attempt) }}" class="mt-8 space-y-3">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                    @foreach ($question->answerOptions as $option)
                        <button name="answer_option_id" value="{{ $option->id }}" class="flex w-full items-center gap-4 rounded-lg border border-zinc-700 bg-zinc-900 p-4 text-left text-lg transition hover:border-cyan-300">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cyan-300 font-bold text-zinc-950">{{ $option->label }}</span>
                            <span>{{ $option->text }}</span>
                        </button>
                    @endforeach
                </form>
            @endif
        </article>
    @endif
</section>
@endsection
