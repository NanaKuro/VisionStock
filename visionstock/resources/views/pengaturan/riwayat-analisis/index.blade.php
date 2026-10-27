@extends('layouts.app', ['title' => 'Pengaturan'])

@section('content')
    <div class="mx-auto w-full max-w-[1200px]">
        <section aria-labelledby="analysis-history-heading">
            @if ($histories->isEmpty())
                <x-empty-state
                    heading="Riwayat analisis belum ada"
                    message="Simpan hasil analisis dari halaman Analisis Stok."
                >
                    <x-slot:actions>
                        <a class="inline-flex min-h-11 items-center justify-center rounded-card bg-brand-primary px-6 py-3 font-bold text-white transition hover:brightness-95" href="{{ route('analisis.index') }}">Analisis Stok</a>
                    </x-slot:actions>
                </x-empty-state>
            @else
                <h1 id="analysis-history-heading" class="mb-6 text-[clamp(1.75rem,3.4vw,3rem)] font-bold tracking-tight">Riwayat Analisis</h1>
                <div class="overflow-x-auto rounded-card">
                    <table class="w-full min-w-[700px] border-separate border-spacing-0 text-left">
                        <thead class="bg-brand-primary text-lg font-bold text-brand-ink">
                            <tr>
                                <th class="rounded-l px-4 py-4">No</th>
                                <th class="px-4 py-4">Tanggal &amp; Jam Analisis</th>
                                <th class="px-4 py-4">Jumlah Produk</th>
                                <th class="rounded-r px-4 py-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($histories as $history)
                                <tr class="[&:nth-child(even)]:bg-brand-zebra">
                                    <td class="px-4 py-4">{{ $histories->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-4">{{ $history->analyzed_at->format('j/n/Y H:i') }}</td>
                                    <td class="px-4 py-4">{{ number_format($history->items_count, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4">
                                        <a class="inline-flex min-h-11 items-center justify-center rounded-card bg-brand-primary px-5 py-2 font-bold text-white transition hover:brightness-95" href="{{ route('pengaturan.riwayat-analisis.show', $history) }}">Lihat Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $histories->links() }}</div>
            @endif
        </section>
    </div>
@endsection
