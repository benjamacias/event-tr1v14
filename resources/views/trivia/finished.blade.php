@extends('layouts.app')

@section('content')
<section class="relative flex min-h-screen overflow-hidden px-5 py-10 text-center">
    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-300 via-emerald-300 to-amber-200"></div>

    <div class="relative mx-auto flex w-full max-w-lg flex-col items-center justify-center">
        <p class="text-sm font-semibold uppercase tracking-wide text-cyan-300">Ianus SA</p>

        <div class="mt-8 flex h-20 w-20 items-center justify-center rounded-full border border-emerald-300/60 bg-emerald-300/15 text-4xl font-black text-emerald-200 shadow-[0_0_40px_rgba(110,231,183,0.18)]">
            &#10003;
        </div>

        <h1 class="mt-8 text-3xl font-black leading-tight sm:text-4xl">
            Gracias por participar.
        </h1>

        <p class="mt-4 text-2xl font-bold leading-snug text-white">
            Completaste todas nuestras preguntas.
        </p>

        <p class="mt-5 max-w-sm text-base leading-7 text-zinc-300">
            Tu participaci&oacute;n qued&oacute; registrada. Muchas gracias por ser parte de la experiencia Ianus.
        </p>

        <div class="mt-10 h-px w-28 bg-gradient-to-r from-transparent via-cyan-300/70 to-transparent"></div>
    </div>
</section>
@endsection
