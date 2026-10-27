@extends('layouts.app', ['title' => 'Pengaturan'])

@section('content')
    <div class="mx-auto w-full max-w-[1200px]">
        <section aria-labelledby="analysis-history-detail-heading">
            <h1 id="analysis-history-detail-heading" class="text-[clamp(1.75rem,3.4vw,3rem)] font-bold tracking-tight">Detail Riwayat Analisis</h1>
            <p class="mt-3 text-base">{{ $history->analyzed_at->format('j/n/Y H:i') }}</p>
            <p class="mt-1 text-sm text-brand-muted">Periode data {{ $history->period_days }} hari · Cakupan rekomendasi {{ $history->coverage_days }} hari</p>

            <div class="mt-6 overflow-x-auto rounded-card">
                <table class="w-full min-w-[760px] border-separate border-spacing-0 text-left">
                    <thead class="bg-brand-primary text-lg font-bold text-brand-ink">
                        <tr>
                            <th class="rounded-l px-4 py-4">No</th>
                            <th class="px-4 py-4">Nama Produk</th>
                            <th class="px-4 py-4">Sisa Stok</th>
                            <th class="px-4 py-4">Rekomendasi Isi Stok</th>
                            <th class="rounded-r px-4 py-4">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history->items as $item)
                            <tr class="[&:nth-child(even)]:bg-brand-zebra">
                                <td class="px-4 py-4">{{ $loop->iteration }}</td>
                                <td class="px-4 py-4 font-medium">{{ $item->product_name }}</td>
                                <td class="px-4 py-4">{{ number_format($item->stock, 0, ',', '.') }}</td>
                                <td class="px-4 py-4">{{ number_format($item->recommended_stock, 0, ',', '.') }}</td>
                                <td class="px-4 py-4">{{ $history->analyzed_at->format('j/n/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <a class="inline-flex min-h-11 items-center justify-center rounded-card border border-brand-lineart/40 px-7 font-bold transition hover:bg-white/35" href="{{ route('pengaturan.riwayat-analisis.index') }}">Kembali</a>
            </div>
        </section>
    </div>
@endsection
