<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title }} | VisionStock</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-dvh bg-brand-surface font-sans text-brand-ink antialiased">
        <div class="min-h-dvh">
            <header class="sticky top-0 z-30 flex min-h-[104px] flex-wrap items-center gap-3 bg-brand-primary px-4 py-3 shadow-[0_4px_5px_rgba(0,0,0,0.25)] md:gap-6 md:px-8">
                <button
                    class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl text-white transition hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white lg:hidden"
                    type="button"
                    aria-label="Buka menu navigasi"
                    aria-controls="main-sidebar"
                    aria-expanded="false"
                    data-menu-button
                >
                    <svg aria-hidden="true" class="size-8" viewBox="0 0 32 32" fill="none">
                        <path d="M5 8h22M5 16h22M5 24h22" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                </button>

                <a class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl text-white transition hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" href="{{ route('dashboard') }}" aria-label="Beranda">
                    <svg aria-hidden="true" class="size-8" viewBox="0 0 32 32" fill="none">
                        <path d="m4 14 12-10 12 10v14H19v-9h-6v9H4V14Z" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
                    </svg>
                </a>

                <a class="text-xl font-bold tracking-tight text-white sm:text-2xl" href="{{ route('dashboard') }}">VisionStock</a>

                @if (request()->routeIs('stok.index'))
                    <form class="ml-auto flex items-center gap-2 sm:gap-4" action="{{ route('stok.index') }}" method="GET" data-topbar-search>
                        <label class="sr-only" for="topbar-search">Cari</label>
                        <input
                            class="hidden h-11 w-40 rounded-card border border-brand-lineart/25 bg-brand-surface px-4 text-base text-brand-ink placeholder:text-brand-muted focus:outline-2 focus:outline-offset-2 focus:outline-white md:block lg:w-56"
                            id="topbar-search"
                            name="search"
                            type="search"
                            value="{{ request('search') }}"
                            placeholder="Cari"
                            data-search-input
                        >
                        @if (request()->filled('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif
                        @if (request()->filled('category_id'))
                            <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                        @endif
                        <button class="inline-flex min-h-11 items-center gap-2 rounded-card bg-brand-surface px-4 text-base font-bold text-brand-ink transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" type="submit" aria-label="Cari" data-search-toggle>
                            <svg aria-hidden="true" class="size-6" viewBox="0 0 24 24" fill="none">
                                <circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="2"/>
                                <path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span class="hidden md:inline">Cari</span>
                        </button>
                        <a class="inline-flex min-h-11 items-center justify-center rounded-card bg-brand-surface px-3 text-sm font-bold text-brand-ink transition hover:bg-white sm:px-5 sm:text-base" href="{{ route('stok.import') }}">Impor Stok</a>
                    </form>
                @elseif (request()->routeIs('transaksi.index'))
                    <form class="ml-auto flex items-center gap-2 sm:gap-4" action="{{ route('transaksi.index') }}" method="GET">
                        <label class="sr-only" for="topbar-transaction-search">Cari transaksi</label>
                        <input
                            class="h-11 w-36 rounded-card border border-brand-lineart/25 bg-brand-surface px-3 text-base text-brand-ink placeholder:text-brand-muted focus:outline-2 focus:outline-offset-2 focus:outline-white sm:w-56"
                            id="topbar-transaction-search"
                            name="search"
                            type="search"
                            value="{{ request('search') }}"
                            placeholder="Cari"
                        >
                        <button class="inline-flex min-h-11 items-center justify-center rounded-card bg-brand-surface px-4 text-base font-bold text-brand-ink transition hover:bg-white" type="submit">Cari</button>
                        <a class="inline-flex min-h-11 items-center justify-center rounded-card bg-brand-surface px-3 text-sm font-bold text-brand-ink transition hover:bg-white sm:px-5 sm:text-base" href="{{ route('transaksi.import') }}">Impor Transaksi</a>
                    </form>
                @endif
            </header>

            <div class="flex min-h-[calc(100dvh-104px)]">
                <div class="fixed inset-0 z-30 hidden bg-black/45 lg:hidden" data-sidebar-overlay></div>

                <aside
                    id="main-sidebar"
                    class="fixed inset-y-0 left-0 z-40 flex w-[min(367px,86vw)] -translate-x-full flex-col bg-brand-primary px-5 pb-6 pt-5 shadow-[4px_0_5px_rgba(0,0,0,0.25)] transition-transform duration-200 lg:sticky lg:top-[104px] lg:min-h-[calc(100dvh-104px)] lg:w-[clamp(15rem,25.5vw,22.9375rem)] lg:shrink-0 lg:translate-x-0 lg:px-8 lg:pb-8 lg:pt-8"
                    data-sidebar
                >
                    <div class="mb-8 flex items-center justify-between lg:justify-center">
                        <h2 class="text-2xl font-bold text-white sm:text-[clamp(1.25rem,2.2vw,2rem)]">{{ $title }}</h2>
                        <button
                            class="inline-flex size-11 items-center justify-center rounded-lg text-white hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-white lg:hidden"
                            type="button"
                            aria-label="Tutup menu navigasi"
                            data-menu-button
                            data-menu-close
                        >
                            <svg aria-hidden="true" class="size-6" viewBox="0 0 24 24" fill="none">
                                <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>

                    <nav class="space-y-3 lg:space-y-16" aria-label="Navigasi utama">
                        @php
                            $navigation = [
                                ['label' => 'Data Stok', 'route' => 'stok.index', 'icon' => 'box'],
                                ['label' => 'Analisis Stok', 'route' => 'analisis.index', 'icon' => 'chart'],
                                ['label' => 'Riwayat Transaksi', 'route' => 'transaksi.index', 'icon' => 'clock'],
                                ['label' => 'Pengaturan', 'route' => 'pengaturan.index', 'icon' => 'settings'],
                            ];
                        @endphp

                        @foreach ($navigation as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                @class([
                                    'flex min-h-[72px] items-center gap-4 rounded-card px-4 text-base font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white md:gap-6 md:px-6 md:text-lg lg:text-[clamp(1rem,1.7vw,1.5rem)]',
                                    'bg-brand-surface text-brand-ink' => request()->routeIs($item['route']) || ($item['route'] === 'stok.index' && request()->routeIs('stok.import')),
                                    'text-white hover:bg-white/20' => ! request()->routeIs($item['route']) && ! ($item['route'] === 'stok.index' && request()->routeIs('stok.import')),
                                ])
                                @if (request()->routeIs($item['route']) || ($item['route'] === 'stok.index' && request()->routeIs('stok.import'))) aria-current="page" @endif
                            >
                                <svg aria-hidden="true" class="size-8 shrink-0 md:size-10" viewBox="0 0 24 24" fill="none">
                                    @switch($item['icon'])
                                        @case('grid')
                                            <rect x="3.5" y="3.5" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                                            <rect x="13.5" y="3.5" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                                            <rect x="3.5" y="13.5" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                                            <rect x="13.5" y="13.5" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                                            @break
                                        @case('box')
                                            <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            <path d="m4.5 7.8 7.5 4.3 7.5-4.3M12 12v8.5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            @break
                                        @case('chart')
                                            <path d="M4 19.5V11m8 8.5V4.5m8 15V8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M2.5 19.5h19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            @break
                                        @case('clock')
                                            <circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            @break
                                        @case('settings')
                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="m19.4 15 .1.1 1.2.9-1.2 2.1-1.5-.6a7.7 7.7 0 0 1-1.7 1l-.3 1.6h-2.4l-.3-1.6a7.7 7.7 0 0 1-1.7-1l-1.5.6-1.2-2.1 1.2-.9a7.1 7.1 0 0 1 0-2l-1.2-.9 1.2-2.1 1.5.6a7.7 7.7 0 0 1 1.7-1l.3-1.6h2.4l.3 1.6a7.7 7.7 0 0 1 1.7 1l1.5-.6 1.2 2.1-1.2.9a7.1 7.1 0 0 1 0 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                    @endswitch
                                </svg>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>

                    <div class="mt-auto flex flex-wrap items-center gap-3 border-t border-white/20 pt-6 md:gap-4">
                        <span class="flex size-15 items-center justify-center rounded-full bg-brand-surface text-lg font-semibold text-brand-ink" aria-hidden="true">A</span>
                        <span class="text-xl font-bold text-white lg:text-[clamp(1.25rem,2.2vw,2rem)]">Admin</span>
                        <form class="ml-auto" action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="min-h-11 rounded-xl px-3 py-2 text-sm font-semibold text-white transition hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-white" type="submit">Keluar</button>
                        </form>
                    </div>
                </aside>

                <main class="min-w-0 flex-1 px-4 py-6 sm:px-6 md:px-8 md:py-10 xl:px-16">
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
