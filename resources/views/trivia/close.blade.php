@extends('layouts.app')

@section('content')
<section class="mx-auto flex min-h-screen w-full max-w-lg flex-col justify-center px-5 text-center">
    <p class="text-sm font-semibold uppercase tracking-wide text-[#00B5E2]">Ianus SA</p>

    @if ($canPlayAgain)
        <h1 class="mt-4 text-3xl font-bold leading-tight">Todavia podes participar en otra trivia.</h1>
        <p class="mt-5 text-white/80">Tenemos otro set disponible para este documento o dispositivo.</p>
        <form method="POST" action="{{ route('play.next', $attempt) }}" class="mt-8">
            @csrf
            <button class="w-full rounded-lg bg-[#00B5E2] px-5 py-4 text-lg font-bold text-[#003B5C]">Continuar con la siguiente trivia</button>
        </form>
    @else
        <h1 class="mt-4 text-3xl font-bold leading-tight">Ya participaste en todas nuestras trivias.</h1>
        <p class="mt-5 text-white/80">Gracias por participar.</p>
        <button type="button" onclick="window.close()" class="mt-8 w-full rounded-lg bg-[#00B5E2] px-5 py-4 text-lg font-bold text-[#003B5C]">Cerrar</button>
    @endif
</section>
@endsection
