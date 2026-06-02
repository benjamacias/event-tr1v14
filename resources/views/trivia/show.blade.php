@extends('layouts.app')

@section('content')
<section class="mx-auto flex min-h-screen w-full max-w-xl flex-col px-5 py-8">
    <div class="mb-5 flex items-center justify-between text-sm text-white/80">
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
                <div class="mt-8 rounded-lg border border-[#00B5E2]/45 bg-[#00B5E2]/15 p-5">
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
                        <p class="mt-3 text-white/90">{{ $explanation }}</p>
                    @endif
                </div>

                @if ($attempt->fresh()->status === \App\Models\Attempt::STATUS_COMPLETED)
                    <a href="{{ route('play.result', $attempt) }}" class="mt-8 block rounded-lg bg-[#00B5E2] px-5 py-4 text-center text-lg font-bold text-[#003B5C]">Ver resultado</a>
                @else
                    <a href="{{ route('play.show', $attempt) }}" class="mt-8 block rounded-lg bg-[#00B5E2] px-5 py-4 text-center text-lg font-bold text-[#003B5C]">Siguiente pregunta</a>
                @endif
            @else
                <form method="POST" action="{{ route('play.answer', $attempt) }}" class="mt-8 space-y-3">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                    @foreach ($question->answerOptions as $option)
                        <button name="answer_option_id" value="{{ $option->id }}" class="flex w-full items-center gap-4 rounded-lg border border-[#00B5E2]/35 bg-[#003B5C] p-4 text-left text-lg transition hover:border-[#00B5E2]">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#00B5E2] font-bold text-[#003B5C]">{{ $option->label }}</span>
                            <span>{{ $option->text }}</span>
                        </button>
                    @endforeach
                </form>
            @endif
        </article>
    @endif
</section>
@endsection
