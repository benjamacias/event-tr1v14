@extends('layouts.app')

@section('content')
<style>
    body {
        overflow: hidden;
    }

    [data-screen-logo-card] img {
        width: min(68%, 260px, 28vh);
        height: auto;
        max-height: calc(100% - 1.5rem);
    }

    [data-screen-qr-code] {
        width: min(82%, 410px, 44vh);
        max-width: calc(100% - 1rem);
    }

    [data-screen-qr-code] svg {
        display: block;
        height: 100%;
        width: 100%;
    }
</style>

<section x-data="leaderboardScreen()" x-init="start()" data-screen-page class="box-border grid h-screen grid-rows-[minmax(0,1fr)] overflow-hidden bg-[#003B5C] p-4 text-white">
    <div class="grid min-h-0 grid-cols-[35%_minmax(0,65%)] gap-6">
        <aside class="grid min-h-0 grid-rows-[minmax(0,0.44fr)_minmax(0,0.56fr)] gap-5">
            <div data-screen-logo-card class="flex min-h-0 items-center justify-center rounded-lg border border-[#00B5E2]/35 bg-[#003B5C] p-5">
                <img src="{{ asset('images/ianus-logo.png') }}" alt="IANUS S.A." class="object-contain">
            </div>

            <div class="grid min-h-0 grid-rows-[minmax(0,1fr)_auto] items-center rounded-lg border border-[#00B5E2]/35 bg-[#003B5C] p-5">
                <div class="flex min-h-0 w-full items-center justify-center">
                    <div data-screen-qr-code class="aspect-square rounded-lg bg-white p-3 text-[#003B5C]">{!! $qrSvg !!}</div>
                </div>
                <p class="pt-4 text-center text-2xl font-bold leading-tight">Escanea y participa</p>
            </div>
        </aside>

        <div class="grid min-h-0 grid-rows-[minmax(0,1fr)_clamp(140px,20vh,230px)] gap-5">
            <section class="grid min-h-0 grid-rows-[auto_minmax(0,1fr)] overflow-hidden rounded-lg border border-[#00B5E2]/35 bg-[#003B5C]">
                <header class="border-b border-[#00B5E2]/35 px-5 py-4">
                    <p class="text-lg font-semibold uppercase tracking-normal text-[#00B5E2]">Pizarra de lideres</p>
                    <h2 class="text-4xl font-black leading-tight tracking-normal">Trivia</h2>
                </header>

                <div data-screen-participants-panel class="min-h-0 overflow-y-auto">
                    <table class="w-full table-fixed text-left">
                        <thead class="sticky top-0 z-10 bg-[#00B5E2] text-[#003B5C]">
                            <tr>
                                <th class="w-[8%] px-5 py-4 text-xl">#</th>
                                <th class="w-[34%] px-5 py-4 text-xl">Participante</th>
                                <th class="w-[16%] px-5 py-4 text-xl">Puntaje</th>
                                <th class="w-[24%] px-5 py-4 text-xl">Set</th>
                                <th class="w-[18%] px-5 py-4 text-xl">Tiempo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#00B5E2]/25 bg-[#003B5C]">
                            <template x-for="(row, index) in rows" :key="index + row.name + row.set">
                                <tr>
                                    <td class="px-5 py-4 text-2xl font-bold" x-text="index + 1"></td>
                                    <td class="truncate px-5 py-4 text-2xl font-semibold" x-text="row.name"></td>
                                    <td class="px-5 py-4 text-2xl" x-text="row.score"></td>
                                    <td class="break-words px-5 py-4 text-xl leading-tight text-white/80" x-text="row.set"></td>
                                    <td class="px-5 py-4 text-2xl font-bold text-[#00B5E2]" x-text="row.time ?? '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>

            <section data-screen-banner-panel class="min-h-0 overflow-hidden rounded-lg border border-[#00B5E2]/35 bg-white p-0">
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
                    <div class="flex h-full w-full items-center justify-center text-4xl font-black text-[#003B5C]">
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
        participantsPanel: null,
        scrollSpeed: 36,
        scrollDelayMs: 60000,
        scrollRemainder: 0,
        lastScrollFrame: null,
        scrollResumeAt: 0,
        start() {
            this.participantsPanel = document.querySelector('[data-screen-participants-panel]');
            this.scheduleScrollDelay(performance.now());
            this.load();
            setInterval(() => this.load(), 3000);
            setInterval(() => this.nextProvider(), 3500);
            window.addEventListener('keydown', (event) => this.handleKeydown(event));
            requestAnimationFrame((timestamp) => this.autoScroll(timestamp));
        },
        async load() {
            const response = await fetch('{{ route('api.leaderboard') }}');
            const payload = await response.json();
            this.rows = payload.data;

            this.$nextTick(() => this.normalizeScroll());
        },
        autoScroll(timestamp) {
            if (! this.participantsPanel) {
                requestAnimationFrame((nextTimestamp) => this.autoScroll(nextTimestamp));
                return;
            }

            if (this.lastScrollFrame === null) {
                this.lastScrollFrame = timestamp;
                requestAnimationFrame((nextTimestamp) => this.autoScroll(nextTimestamp));
                return;
            }

            if (timestamp < this.scrollResumeAt) {
                this.lastScrollFrame = timestamp;
                requestAnimationFrame((nextTimestamp) => this.autoScroll(nextTimestamp));
                return;
            }

            const elapsedSeconds = Math.min((timestamp - this.lastScrollFrame) / 1000, 0.1);
            this.lastScrollFrame = timestamp;

            if (this.canScrollParticipants()) {
                const maxScrollTop = this.maxParticipantsScrollTop();

                if (this.participantsPanel.scrollTop >= maxScrollTop - 1) {
                    this.scrollParticipantsToTop();
                    this.scheduleScrollDelay(timestamp);
                } else {
                    const scrollDistance = (this.scrollSpeed * elapsedSeconds) + this.scrollRemainder;
                    const scrollPixels = Math.floor(scrollDistance);
                    this.scrollRemainder = scrollDistance - scrollPixels;

                    if (scrollPixels === 0) {
                        requestAnimationFrame((nextTimestamp) => this.autoScroll(nextTimestamp));
                        return;
                    }

                    this.participantsPanel.scrollTop = Math.min(
                        this.participantsPanel.scrollTop + scrollPixels,
                        maxScrollTop
                    );
                }
            } else {
                this.scrollRemainder = 0;
                this.participantsPanel.scrollTop = 0;
            }

            requestAnimationFrame((nextTimestamp) => this.autoScroll(nextTimestamp));
        },
        handleKeydown(event) {
            if (! document.hasFocus() || ! ['ArrowUp', 'PageUp', 'Home'].includes(event.key)) {
                return;
            }

            event.preventDefault();
            this.scrollParticipantsToTop();
            this.scheduleScrollDelay(performance.now());
        },
        scrollParticipantsToTop() {
            if (this.participantsPanel) {
                this.scrollRemainder = 0;
                this.participantsPanel.scrollTop = 0;
            }
        },
        scheduleScrollDelay(timestamp) {
            this.scrollRemainder = 0;
            this.lastScrollFrame = timestamp;
            this.scrollResumeAt = timestamp + this.scrollDelayMs;
        },
        normalizeScroll() {
            if (! this.participantsPanel) {
                return;
            }

            if (! this.canScrollParticipants()) {
                this.participantsPanel.scrollTop = 0;
                return;
            }

            this.participantsPanel.scrollTop = Math.min(
                this.participantsPanel.scrollTop,
                this.maxParticipantsScrollTop()
            );
        },
        canScrollParticipants() {
            return this.maxParticipantsScrollTop() > 0;
        },
        maxParticipantsScrollTop() {
            if (! this.participantsPanel) {
                return 0;
            }

            return Math.max(0, this.participantsPanel.scrollHeight - this.participantsPanel.clientHeight);
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
