@extends('layouts.app')

@section('content')
<section class="mx-auto flex min-h-screen w-full max-w-lg flex-col justify-center px-5 py-8">
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-wide text-[#00B5E2]">Ianus SA</p>
        <h1 class="mt-3 text-3xl font-bold leading-tight">{{ $initialMessage }}</h1>
        <p class="mt-3 text-lg text-white/85">{{ $formText }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-[#00B5E2]/45 bg-[#00B5E2]/15 p-4 text-sm text-white">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('participants.store') }}" class="space-y-4">
        @csrf
        <label class="block">
            <span class="text-sm font-medium text-white/85">Nombre y apellido</span>
            <input name="full_name" value="{{ old('full_name') }}" required class="mt-2 w-full rounded-lg border border-[#00B5E2]/35 bg-[#003B5C] px-4 py-4 text-lg outline-none focus:border-[#00B5E2]">
        </label>
        <label class="block">
            <span class="text-sm font-medium text-white/85">Numero de documento</span>
            <input name="document_number" value="{{ old('document_number') }}" required inputmode="numeric" autocomplete="off" class="mt-2 w-full rounded-lg border border-[#00B5E2]/35 bg-[#003B5C] px-4 py-4 text-lg outline-none focus:border-[#00B5E2]">
        </label>
        <label class="block">
            <span class="text-sm font-medium text-white/85">Mail</span>
            <input type="email" name="email" value="{{ old('email') }}" required class="mt-2 w-full rounded-lg border border-[#00B5E2]/35 bg-[#003B5C] px-4 py-4 text-lg outline-none focus:border-[#00B5E2]">
        </label>
        <label class="block">
            <span class="text-sm font-medium text-white/85">Celular</span>
            <input name="phone" value="{{ old('phone') }}" required class="mt-2 w-full rounded-lg border border-[#00B5E2]/35 bg-[#003B5C] px-4 py-4 text-lg outline-none focus:border-[#00B5E2]">
        </label>
        <label class="block">
            <span class="text-sm font-medium text-white/85">Institución/cargo</span>
            <input name="institution_role" value="{{ old('institution_role') }}" class="mt-2 w-full rounded-lg border border-[#00B5E2]/35 bg-[#003B5C] px-4 py-4 text-lg outline-none focus:border-[#00B5E2]">
        </label>
        <label class="flex gap-3 rounded-lg border border-[#00B5E2]/35 bg-[#003B5C] p-4">
            <input type="checkbox" name="consent_accepted" value="1" required class="mt-1 h-5 w-5 rounded border-[#00B5E2]">
            <span class="text-sm leading-6 text-white/85">{{ $consentText }}</span>
        </label>
        <button class="w-full rounded-lg bg-[#00B5E2] px-5 py-4 text-lg font-bold text-[#003B5C]">Empezar trivia</button>
    </form>
</section>
@endsection
