@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-[1200px]">
        @if ($title === 'Dashboard')
            <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" aria-label="Ringkasan data">
                <article class="flex min-h-64 flex-col justify-center rounded-card bg-brand-primary p-6 text-white shadow-sm sm:col-span-2 sm:min-h-[19rem] sm:p-8 lg:p-12">
                    <h1 id="welcome-heading" class="text-[clamp(1.75rem,3.4vw,3rem)] font-bold leading-tight">Halo, Admin!</h1>
                    <p class="mt-4 text-[clamp(1.25rem,2.2vw,2rem)] font-bold leading-tight">Ingin melakukan apa hari ini?</p>
                </article>

                @foreach ([
                    ['label' => 'Total Riwayat Transaksi', 'value' => $metrics['transaction_records']],
                    ['label' => 'Total Produk', 'value' => $metrics['products']],
                    ['label' => 'Jumlah Produk', 'value' => $metrics['units_in_stock']],
                    ['label' => 'Jumlah Transaksi', 'value' => $metrics['units_sold']],
                ] as $metric)
                    <article class="flex flex-col gap-3" aria-label="{{ $metric['label'] }}">
                        <h2 class="min-h-12 text-center text-lg font-bold leading-tight sm:min-h-16 sm:text-xl">{{ $metric['label'] }}</h2>
                        <div class="flex aspect-[264/204] min-h-40 items-center justify-center rounded-card bg-brand-primary p-5 text-white shadow-sm">
                            <p class="text-[clamp(2rem,4vw,3.5rem)] font-bold tracking-tight">{{ number_format($metric['value'], 0, ',', '.') }}</p>
                        </div>
                    </article>
                @endforeach
            </section>
        @else
            <section class="rounded-card bg-white/80 p-6 shadow-sm sm:p-8">
                <h1 class="text-[clamp(1.75rem,3.4vw,3rem)] font-bold tracking-tight">{{ $title }}</h1>
                <p class="mt-6 text-brand-ink/75">Halaman ini akan dilengkapi pada tahap berikutnya.</p>
            </section>
        @endif
    </div>
@endsection
