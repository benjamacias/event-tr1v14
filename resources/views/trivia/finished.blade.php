@extends('layouts.app')

@section('content')
<section class="relative flex min-h-screen overflow-hidden px-5 py-10 text-center">
    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#003B5C] via-[#00B5E2] to-[#003B5C]"></div>

    <div class="relative mx-auto flex w-full max-w-lg flex-col items-center justify-center">
        <p class="text-sm font-semibold uppercase tracking-wide text-[#00B5E2]">Ianus SA</p>

        <div class="mt-8 flex h-20 w-20 items-center justify-center rounded-full border border-[#00B5E2]/60 bg-[#00B5E2]/15 text-4xl font-black text-[#00B5E2] shadow-[0_0_40px_rgba(0,181,226,0.18)]">
            &#10003;
        </div>

        <h1 class="mt-8 text-3xl font-black leading-tight sm:text-4xl">
            Gracias por participar.
        </h1>

        <p class="mt-4 text-2xl font-bold leading-snug text-white">
            Completaste todas nuestras preguntas.
        </p>

        <p class="mt-5 max-w-sm text-base leading-7 text-white/80">
            Tu participaci&oacute;n qued&oacute; registrada. Muchas gracias por ser parte de la experiencia Ianus.
        </p>

        <div class="mt-10 h-px w-28 bg-gradient-to-r from-transparent via-[#00B5E2]/70 to-transparent"></div>
    </div>
</section>
@endsection
