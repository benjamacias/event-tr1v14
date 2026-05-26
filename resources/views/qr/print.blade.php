@extends('layouts.app')

@section('content')
<section class="flex min-h-screen flex-col items-center justify-center bg-white px-8 py-10 text-center text-zinc-950">
    @if ($logoPath)
        <img src="{{ asset('storage/'.$logoPath) }}" alt="Ianus SA" class="mb-8 max-h-32 object-contain">
    @else
        <div class="mb-8 text-6xl font-black">IANUS SA</div>
    @endif
    <div class="rounded-lg border-4 border-zinc-950 p-6">{!! $qrSvg !!}</div>
    <p class="mt-8 max-w-3xl text-4xl font-bold leading-tight">{{ $printText }}</p>
    <button onclick="window.print()" class="no-print mt-8 rounded-lg bg-zinc-950 px-6 py-3 font-bold text-white">Imprimir</button>
</section>
@endsection
