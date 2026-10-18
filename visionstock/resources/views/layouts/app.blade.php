<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title }} | VisionStock</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-brand-surface font-sans text-brand-ink antialiased">
        <div class="min-h-screen">
            <header class="relative z-30 flex h-[104px] items-center gap-6 bg-brand-primary px-5 md:px-8">
                <button
                    class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl text-brand-ink transition hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink"
                    type="button"
                    aria-label="Buka menu navigasi"
                    aria-controls="main-sidebar"
                    aria-expanded="false"
                    data-menu-button
                >
                    <svg aria-hidden="true" class="size-7" viewBox="0 0 32 32" fill="none">
                        <path d="M5 8h22M5 16h22M5 24h22" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                </button>

                <a class="flex items-center gap-3 text-2xl font-semibold tracking-tight text-brand-ink" href="{{ route('dashboard') }}">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-white/30">
                        <svg aria-hidden="true" class="size-7" viewBox="0 0 32 32" fill="none">
                            <path d="M5 9.5 16 4l11 5.5v13L16 28 5 22.5v-13Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="m5.5 9.5 10.5 5 10.5-5M16 15v12" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span>VisionStock</span>
                </a>

                <div class="ml-auto hidden items-center gap-3 sm:flex">
                    <span class="text-sm font-medium text-brand-ink/80">Admin</span>
                    <span class="flex size-11 items-center justify-center rounded-full bg-brand-ink text-sm font-semibold text-white" aria-hidden="true">A</span>
                </div>
            </header>

            <div class="flex min-h-[calc(100vh-104px)]">
                <div class="fixed inset-0 z-30 hidden bg-brand-ink/40 md:hidden" data-sidebar-overlay></div>

                <aside
                    id="main-sidebar"
                    class="fixed inset-y-0 left-0 z-40 flex w-[min(367px,86vw)] -translate-x-full flex-col bg-brand-primary px-5 pb-6 pt-5 transition-transform duration-200 md:static md:min-h-[calc(100vh-104px)] md:w-[367px] md:shrink-0 md:translate-x-0 md:px-8 md:pb-8"
                    data-sidebar
                >
                    <div class="mb-8 flex items-center justify-between md:hidden">
                        <span class="text-lg font-semibold">Menu</span>
                        <button
                            class="rounded-lg p-2 hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-brand-ink"
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

                    <nav class="space-y-3" aria-label="Navigasi utama">
                        @php
                            $navigation = [
                                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid'],
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
                                    'flex min-h-[72px] items-center gap-4 rounded-2xl px-5 text-base font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink md:px-6',
                                    'bg-brand-surface text-brand-ink' => request()->routeIs($item['route']),
                                    'text-brand-ink/80 hover:bg-white/20 hover:text-brand-ink' => ! request()->routeIs($item['route']),
                                ])
                                @if (request()->routeIs($item['route'])) aria-current="page" @endif
                            >
                                <svg aria-hidden="true" class="size-6 shrink-0" viewBox="0 0 24 24" fill="none">
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

                    <div class="mt-auto flex items-center gap-3 border-t border-brand-ink/15 pt-6">
                        <span class="flex size-11 items-center justify-center rounded-full bg-brand-surface text-sm font-semibold">A</span>
                        <span class="text-sm font-semibold">Admin</span>
                        <form class="ml-auto" action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="rounded-xl px-3 py-2 text-sm font-semibold text-brand-ink transition hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-brand-ink" type="submit">Keluar</button>
                        </form>
                    </div>
                </aside>

                <main class="min-w-0 flex-1 px-5 py-8 md:px-8 md:py-10">
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
