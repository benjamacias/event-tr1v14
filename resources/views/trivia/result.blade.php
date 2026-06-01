@extends('layouts.app')

@section('content')
@php
    $message = $attempt->correct_answers_count === 5
        ? str_replace(':time', $attempt->formattedTime(), $perfectMessage)
        : str_replace(':score', (string) $attempt->correct_answers_count, $partialMessage);
@endphp
<section class="mx-auto flex min-h-screen w-full max-w-lg flex-col justify-center px-5 text-center">
    <p class="text-sm font-semibold uppercase tracking-wide text-cyan-300">Ianus SA</p>
    <h1 class="mt-4 text-3xl font-bold leading-tight">{{ $message }}</h1>
    <p class="mt-5 text-zinc-300">Tu participación quedó registrada.</p>
    <form method="POST" action="{{ route('play.close', $attempt) }}" class="mt-8">
        @csrf
        <button class="w-full rounded-lg bg-cyan-300 px-5 py-4 text-lg font-bold text-zinc-950">Cerrar</button>
    </form>
</section>
@endsection
