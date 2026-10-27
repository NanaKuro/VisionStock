@extends('layouts.app', ['title' => 'Analisis Stok'])

@section('content')
    <div class="mx-auto w-full max-w-[1200px]">
        @if (session('analysis_history_success'))
            <dialog class="m-auto w-[min(92vw,739px)] rounded-card bg-brand-surface p-8 text-center text-brand-ink shadow-2xl backdrop:bg-black/45 sm:p-12" data-analysis-history-success>
                <div class="mx-auto flex size-[clamp(6rem,14vw,10.25rem)] items-center justify-center rounded-full border-[5px] border-emerald-700 text-5xl text-emerald-700" aria-hidden="true">✓</div>
                <h2 class="mt-6 text-2xl font-bold">{{ session('analysis_history_success') }}</h2>
                <a class="mt-8 inline-flex min-h-11 items-center justify-center rounded-card bg-brand-primary px-8 py-3 font-bold text-white transition hover:brightness-95" href="{{ route('pengaturan.riwayat-analisis.index') }}">Selesai</a>
            </dialog>
        @endif
        @if (session('analysis_history_error'))
            <dialog class="m-auto w-[min(92vw,739px)] rounded-card bg-brand-surface p-8 text-center text-brand-ink shadow-2xl backdrop:bg-black/45 sm:p-12" data-analysis-history-error>
                <div class="mx-auto flex size-[clamp(6rem,14vw,10.25rem)] items-center justify-center rounded-full border-[5px] border-brand-danger text-5xl text-brand-danger" aria-hidden="true">×</div>
                <h2 class="mt-6 text-2xl font-bold">{{ session('analysis_history_error') }}</h2>
                <button class="mt-8 min-h-11 rounded-card bg-brand-primary px-8 py-3 font-bold text-white transition hover:brightness-95" type="button" data-analysis-history-error-close>Coba lagi</button>
            </dialog>
        @endif

        @if ($products->isEmpty())
            <x-empty-state
                heading="Data stok belum ada nih"
                message="Ayo lakukan input stok atau impor dari pencatatan sebelumnya."
            >
                <x-slot:actions>
                    <a class="inline-flex min-h-11 items-center justify-center rounded-card bg-brand-primary px-6 py-3 font-bold text-white transition hover:brightness-95" href="{{ route('stok.index') }}">Data Stok</a>
                    <a class="inline-flex min-h-11 items-center justify-center rounded-card border-2 border-brand-primary px-6 py-3 font-bold text-brand-ink transition hover:bg-white/30" href="{{ route('stok.import') }}">Impor Stok</a>
                </x-slot:actions>
            </x-empty-state>
        @else
            <section aria-labelledby="analysis-heading">
                <h1 id="analysis-heading" class="mb-6 text-[clamp(1.75rem,3.4vw,3rem)] font-bold tracking-tight">Stok Saat Ini</h1>
                <div class="overflow-x-auto rounded-card">
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
                            @foreach ($products as $product)
                                <tr class="[&:nth-child(even)]:bg-brand-zebra">
                                    <td class="px-4 py-4">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-4 font-medium">{{ $product->name }}</td>
                                    <td class="px-4 py-4">{{ number_format($product->stock, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4">
                                        {{ number_format($product->recommended_stock, 0, ',', '.') }}
                                        @if ($product->has_insufficient_history)
                                            <span class="block text-sm text-brand-muted">Data belum cukup</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">{{ now()->format('j/n/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:gap-6">
                    <a class="inline-flex min-h-11 items-center justify-center rounded-card bg-brand-primary px-6 py-3 font-bold text-white transition hover:brightness-95" href="{{ route('stok.index') }}">Edit Stok</a>
                    <button class="inline-flex min-h-11 items-center justify-center rounded-card bg-brand-primary px-6 py-3 font-bold text-white transition hover:brightness-95" type="button" data-analysis-history-open>Simpan Riwayat</button>
                </div>
            </section>
        @endif
    </div>

    @if (! $products->isEmpty())
        <dialog class="m-auto w-[min(92vw,739px)] rounded-card bg-brand-surface p-6 text-center text-brand-ink shadow-2xl backdrop:bg-black/45 sm:p-12" aria-labelledby="analysis-history-confirm-heading" data-analysis-history-confirm>
            <h2 class="text-2xl font-bold" id="analysis-history-confirm-heading">Ingin menyimpan analisis ini ke riwayat?</h2>
            <form class="mt-8 flex flex-col justify-center gap-3 sm:flex-row sm:gap-6" action="{{ route('analisis.riwayat.store') }}" method="POST" data-analysis-history-form>
                @csrf
                <button class="min-h-11 rounded-card bg-brand-primary px-7 py-3 font-bold text-white transition hover:brightness-95" type="submit" data-analysis-history-submit>Simpan Riwayat</button>
                <button class="min-h-11 rounded-card border border-brand-lineart/40 px-7 py-3 font-bold transition hover:bg-white/35" type="button" data-analysis-history-close>Batal</button>
            </form>
        </dialog>
    @endif
@endsection
