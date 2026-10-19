@extends('layouts.guest', ['title' => 'Solusi Permasalahan Stok'])

@section('content')
    <div class="min-h-screen">
        <header class="flex min-h-[106px] items-center justify-between bg-brand-primary px-4 shadow-[0_4px_5px_rgba(0,0,0,0.25)] sm:px-8 lg:px-16">
            <a class="text-xl font-bold tracking-tight text-white sm:text-3xl" href="{{ route('home') }}">VisionStock</a>
            <nav class="flex items-center gap-2 sm:gap-5" aria-label="Akun">
                <a class="inline-flex min-h-11 items-center justify-center rounded-card px-3 text-sm font-bold text-brand-ink transition hover:bg-white/25 sm:w-28 sm:px-4 sm:text-base" href="{{ route('login') }}">Masuk</a>
                <a class="inline-flex min-h-11 items-center justify-center rounded-card bg-brand-surface px-3 text-sm font-bold text-brand-ink transition hover:bg-white sm:w-28 sm:px-4 sm:text-base" href="{{ route('register') }}">Daftar</a>
            </nav>
        </header>

        <main class="mx-auto grid min-h-[calc(100dvh-6.625rem)] max-w-[1440px] items-center gap-8 px-4 py-8 sm:px-8 lg:grid-cols-[minmax(0,448fr)_minmax(0,661fr)] lg:gap-12 lg:px-16 lg:py-16">
            <section class="max-w-2xl lg:pr-6">
                <h1 class="text-[clamp(2rem,4vw,3.5rem)] font-bold leading-[1.11] tracking-tight">
                    Solusi<br class="hidden sm:block"> Permasalahan Stok
                </h1>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a class="inline-flex min-h-11 w-full items-center justify-center rounded-card bg-brand-primary px-7 py-3 font-bold text-white shadow-sm transition hover:brightness-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink sm:w-auto sm:min-w-[15.4375rem]" href="{{ route('register') }}">Coba Sekarang</a>
                </div>
            </section>

            <div class="mx-auto w-full max-w-[661px] overflow-hidden rounded-card shadow-[0_8px_8px_rgba(0,0,0,0.25)]">
                <img
                    class="aspect-square w-full object-cover"
                    src="{{ asset('images/image-landing.png') }}"
                    alt="Rak penyimpanan produk di toko"
                >
            </div>
        </main>
    </div>
@endsection
