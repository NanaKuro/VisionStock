@extends('layouts.guest', ['title' => 'Solusi Permasalahan Stok'])

@section('content')
    <div class="min-h-screen">
        <header class="flex h-24 items-center justify-between bg-brand-primary px-6 shadow-sm sm:px-10 lg:px-20">
            <a class="text-2xl font-bold tracking-tight text-white sm:text-3xl" href="{{ route('home') }}">VisionStock</a>
            <nav class="flex items-center gap-3 sm:gap-5" aria-label="Akun">
                <a class="rounded-card px-4 py-2 font-semibold text-brand-ink transition hover:bg-white/25" href="{{ route('login') }}">Masuk</a>
                <a class="rounded-card bg-brand-surface px-4 py-2 font-semibold text-brand-ink transition hover:bg-white" href="{{ route('register') }}">Daftar</a>
            </nav>
        </header>

        <main class="mx-auto grid min-h-[calc(100vh-6rem)] max-w-[1440px] items-center gap-10 px-6 py-12 sm:px-10 lg:grid-cols-[1fr_0.9fr] lg:px-20 lg:py-16">
            <section class="max-w-2xl">
                <h1 class="text-4xl font-bold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                    Solusi<br class="hidden sm:block"> Permasalahan Stok
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-brand-ink/80">
                    Catat persediaan dan transaksi, pantau kebutuhan produk, lalu rencanakan pengisian stok dengan lebih mudah.
                </p>
                <div class="mt-9 flex flex-wrap gap-4">
                    <a class="rounded-card bg-brand-primary px-7 py-3.5 font-semibold text-white shadow-sm transition hover:brightness-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink" href="{{ route('login') }}">Masuk</a>
                    <a class="rounded-card border-2 border-brand-primary px-7 py-3.5 font-semibold text-brand-ink transition hover:bg-white/30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink" href="{{ route('register') }}">Daftar</a>
                </div>
            </section>

            <div class="overflow-hidden rounded-[2rem] shadow-lg">
                <img
                    class="aspect-[4/3] w-full object-cover lg:aspect-[1/1.08]"
                    src="{{ asset('images/image-landing.png') }}"
                    alt="Rak penyimpanan produk di toko"
                >
            </div>
        </main>
    </div>
@endsection
