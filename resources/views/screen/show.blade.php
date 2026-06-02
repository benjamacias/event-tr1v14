@extends('layouts.app')

@section('content')
<style>
    body {
        overflow: hidden;
    }
</style>

<section x-data="leaderboardScreen()" x-init="start()" data-screen-page class="box-border grid h-screen grid-rows-[auto_minmax(0,1fr)] gap-4 overflow-hidden bg-zinc-950 p-4 text-white">
    <header class="flex items-center justify-center rounded-lg border border-zinc-800 bg-zinc-900 px-6 py-3">
        <h1 class="text-center text-5xl font-black leading-none tracking-normal text-white">IANUS S.A.</h1>
    </header>

    <div class="grid min-h-0 grid-cols-[35%_minmax(0,65%)] gap-6">
        <aside class="grid min-h-0 grid-rows-[minmax(0,0.42fr)_minmax(0,0.58fr)] gap-5">
            <div class="flex min-h-0 items-center justify-center rounded-lg border border-zinc-800 bg-zinc-900 p-5">
                @if ($logoPath)
                    <img src="{{ asset('storage/'.$logoPath) }}" alt="Ianus S.A." class="max-h-full max-w-full object-contain">
                @else
                    <div class="text-center text-5xl font-black leading-none tracking-normal">IANUS S.A.</div>
                @endif
            </div>

            <div class="grid min-h-0 grid-rows-[minmax(0,1fr)_auto] items-center rounded-lg border border-zinc-800 bg-zinc-900 p-5">
                <div class="flex min-h-0 w-full items-center justify-center">
                    <div class="aspect-square h-full max-h-full max-w-full rounded-lg bg-white p-3 text-zinc-950 [&_svg]:h-full [&_svg]:w-full">{!! $qrSvg !!}</div>
                </div>
                <p class="pt-4 text-center text-2xl font-bold leading-tight">Escanea y participa</p>
            </div>
        </aside>

        <div class="grid min-h-0 grid-rows-[minmax(0,1fr)_clamp(140px,20vh,230px)] gap-5">
            <section class="grid min-h-0 grid-rows-[auto_minmax(0,1fr)] overflow-hidden rounded-lg border border-zinc-800 bg-zinc-900">
                <header class="border-b border-zinc-800 px-5 py-4">
                    <p class="text-lg font-semibold uppercase tracking-normal text-cyan-300">Pizarra de lideres</p>
                    <h2 class="text-4xl font-black leading-tight tracking-normal">Trivia Ianus S.A.</h2>
                </header>

                <div data-screen-participants-panel class="min-h-0 overflow-y-auto">
                    <table class="w-full table-fixed text-left">
                        <thead class="sticky top-0 z-10 bg-cyan-300 text-zinc-950">
                            <tr>
                                <th class="w-[8%] px-5 py-4 text-xl">#</th>
                                <th class="w-[42%] px-5 py-4 text-xl">Participante</th>
                                <th class="w-[16%] px-5 py-4 text-xl">Puntaje</th>
                                <th class="w-[16%] px-5 py-4 text-xl">Set</th>
                                <th class="w-[18%] px-5 py-4 text-xl">Tiempo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800 bg-zinc-900">
                            <template x-for="(row, index) in rows" :key="index + row.name + row.set">
                                <tr>
                                    <td class="px-5 py-4 text-2xl font-bold" x-text="index + 1"></td>
                                    <td class="truncate px-5 py-4 text-2xl font-semibold" x-text="row.name"></td>
                                    <td class="px-5 py-4 text-2xl" x-text="row.score"></td>
                                    <td class="truncate px-5 py-4 text-xl text-zinc-300" x-text="row.set"></td>
                                    <td class="px-5 py-4 text-2xl font-bold text-cyan-300" x-text="row.time ?? '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>

            <section data-screen-banner-panel class="min-h-0 overflow-hidden rounded-lg border border-zinc-800 bg-white p-0">
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
        </div>
    </div>
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
