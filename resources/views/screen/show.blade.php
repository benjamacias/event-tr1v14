@extends('layouts.app')

@section('content')
<section x-data="leaderboardScreen()" x-init="start()" class="flex h-screen overflow-hidden flex-col bg-zinc-950 p-6 text-white">
    <div class="grid min-h-0 flex-1 grid-cols-[400px_1fr] gap-6">
        <aside class="flex flex-col items-center justify-between rounded-lg border border-zinc-800 bg-zinc-900 p-6">
            <div class="w-full text-center">
                @if ($logoPath)
                    <img src="{{ asset('storage/'.$logoPath) }}" alt="Ianus SA" class="mx-auto max-h-32 object-contain">
                @else
                    <div class="text-5xl font-black">IANUS SA</div>
                @endif
            </div>

            <div class="rounded-lg bg-white p-4 text-zinc-950">{!! $qrSvg !!}</div>
            <p class="text-center text-2xl font-bold">Escanea y participa</p>
        </aside>

        <div class="flex min-h-0 flex-col">
            <header class="mb-5">
                <p class="text-lg font-semibold uppercase tracking-wide text-cyan-300">Pizarra de lideres</p>
                <h1 class="text-5xl font-black">Trivia Ianus SA</h1>
            </header>

            <div class="min-h-0 flex-1 overflow-hidden rounded-lg border border-zinc-800">
                <table class="w-full text-left">
                    <thead class="bg-cyan-300 text-zinc-950">
                        <tr>
                            <th class="px-5 py-4 text-xl">#</th>
                            <th class="px-5 py-4 text-xl">Participante</th>
                            <th class="px-5 py-4 text-xl">Puntaje</th>
                            <th class="px-5 py-4 text-xl">Set</th>
                            <th class="px-5 py-4 text-xl">Tiempo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800 bg-zinc-900">
                        <template x-for="(row, index) in rows" :key="index + row.name + row.set">
                            <tr>
                                <td class="px-5 py-4 text-2xl font-bold" x-text="index + 1"></td>
                                <td class="px-5 py-4 text-2xl font-semibold" x-text="row.name"></td>
                                <td class="px-5 py-4 text-2xl" x-text="row.score"></td>
                                <td class="px-5 py-4 text-xl text-zinc-300" x-text="row.set"></td>
                                <td class="px-5 py-4 text-2xl font-bold text-cyan-300" x-text="row.time ?? '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <section class="mt-6 h-[24vh] min-h-[180px] shrink-0 overflow-hidden rounded-lg border border-zinc-800 bg-white p-0">
        <template x-if="currentProvider()">
            <div class="flex h-full w-full items-center justify-center">
                <img
                    :src="currentProvider().url"
                    :alt="currentProvider().name"
                    class="h-full w-full object-contain"
                    x-on:error="markProviderFailed(currentProvider().url)"
                >
            </div>
        </template>
        <template x-if="! currentProvider()">
            <div class="flex h-full w-full items-center justify-center text-4xl font-black text-zinc-900">
                Publicidad
            </div>
        </template>
    </section>
</section>

<script>
function leaderboardScreen() {
    return {
        rows: [],
        providerLogos: @json($providerAds),
        providerIndex: 0,
        failedProviderUrls: {},
        start() {
            this.load();
            setInterval(() => this.load(), 3000);
            setInterval(() => this.nextProvider(), 3500);
        },
        async load() {
            const response = await fetch('{{ route('api.leaderboard') }}');
            const payload = await response.json();
            this.rows = payload.data;
        },
        availableProviders() {
            return this.providerLogos.filter((provider) => provider.url && ! this.failedProviderUrls[provider.url]);
        },
        currentProvider() {
            const providers = this.availableProviders();

            return providers.length ? providers[this.providerIndex % providers.length] : null;
        },
        nextProvider() {
            const providers = this.availableProviders();
            this.providerIndex = providers.length ? (this.providerIndex + 1) % providers.length : 0;
        },
        markProviderFailed(url) {
            this.failedProviderUrls[url] = true;
            this.nextProvider();
        }
    };
}
</script>
@endsection
