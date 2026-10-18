@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-6xl">
        <p class="mb-2 text-sm font-semibold uppercase tracking-[0.16em] text-brand-ink/65">VisionStock</p>
        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ $title }}</h1>

        @if ($title === 'Dashboard')
            <section class="mt-8 rounded-card bg-brand-primary p-6 text-white shadow-sm sm:p-8 lg:p-10" aria-labelledby="welcome-heading">
                <h2 id="welcome-heading" class="text-2xl font-semibold sm:text-3xl">Halo, Admin! Ingin melakukan apa hari ini?</h2>
                <p class="mt-3 max-w-2xl leading-7 text-white/85">
                    Kelola data stok, pantau rekomendasi pengisian, dan catat transaksi dalam satu tempat.
                </p>
            </section>

            <section class="mt-8" aria-label="Ringkasan data">
                <div class="grid gap-5 sm:grid-cols-2">
                    <article class="rounded-card bg-white/85 p-6 shadow-sm sm:p-7">
                        <h2 class="text-base font-semibold text-brand-ink/75">Total Riwayat Transaksi</h2>
                        <p class="mt-4 text-4xl font-bold tracking-tight">{{ number_format($metrics['transaction_records'], 0, ',', '.') }}</p>
                        <p class="mt-2 text-sm text-brand-ink/65">Catatan transaksi tersimpan</p>
                    </article>
                    <article class="rounded-card bg-white/85 p-6 shadow-sm sm:p-7">
                        <h2 class="text-base font-semibold text-brand-ink/75">Total Produk</h2>
                        <p class="mt-4 text-4xl font-bold tracking-tight">{{ number_format($metrics['products'], 0, ',', '.') }}</p>
                        <p class="mt-2 text-sm text-brand-ink/65">Jenis produk terdaftar</p>
                    </article>
                    <article class="rounded-card bg-white/85 p-6 shadow-sm sm:p-7">
                        <h2 class="text-base font-semibold text-brand-ink/75">Jumlah Produk</h2>
                        <p class="mt-4 text-4xl font-bold tracking-tight">{{ number_format($metrics['units_in_stock'], 0, ',', '.') }}</p>
                        <p class="mt-2 text-sm text-brand-ink/65">Total unit stok saat ini</p>
                    </article>
                    <article class="rounded-card bg-white/85 p-6 shadow-sm sm:p-7">
                        <h2 class="text-base font-semibold text-brand-ink/75">Jumlah Transaksi</h2>
                        <p class="mt-4 text-4xl font-bold tracking-tight">{{ number_format($metrics['units_sold'], 0, ',', '.') }}</p>
                        <p class="mt-2 text-sm text-brand-ink/65">Unit produk terjual</p>
                    </article>
                </div>
            </section>
        @else
            <section class="mt-8 rounded-card bg-white/80 p-6 shadow-sm sm:p-8">
                <p class="text-brand-ink/75">Halaman ini akan dilengkapi pada tahap berikutnya.</p>
            </section>
        @endif
    </div>
@endsection
