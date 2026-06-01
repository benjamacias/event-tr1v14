@extends('layouts.app')

@section('content')
<section class="mx-auto flex min-h-screen w-full max-w-lg flex-col justify-center px-5 text-center">
    <p class="text-sm font-semibold uppercase tracking-wide text-cyan-300">Ianus SA</p>

    @if ($canPlayAgain)
        <h1 class="mt-4 text-3xl font-bold leading-tight">Todavia podes participar en otra trivia.</h1>
        <p class="mt-5 text-zinc-300">Tenemos otro set disponible para este documento o dispositivo.</p>
        <a href="{{ route('participants.create') }}" class="mt-8 block rounded-lg bg-cyan-300 px-5 py-4 text-center text-lg font-bold text-zinc-950">Participar en otra trivia</a>
    @else
        <h1 class="mt-4 text-3xl font-bold leading-tight">Ya participaste en todas nuestras trivias.</h1>
        <p class="mt-5 text-zinc-300">Gracias por participar.</p>
        <button type="button" onclick="window.close()" class="mt-8 w-full rounded-lg bg-cyan-300 px-5 py-4 text-lg font-bold text-zinc-950">Cerrar</button>
    @endif
</section>
@endsection
