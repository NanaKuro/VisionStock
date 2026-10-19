@extends('layouts.app', ['title' => 'Riwayat Transaksi'])

@section('content')
    <div class="mx-auto w-full max-w-[1200px]">
        @if ($transactions->isEmpty())
            <x-empty-state
                heading="Riwayat transaksi belum ada nih"
                message="Ayo lakukan input transaksi atau impor transaksi sebelumnya"
            />
        @else
            <section aria-labelledby="transactions-heading">
                <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                    <h1 id="transactions-heading" class="text-[clamp(1.75rem,3.4vw,3rem)] font-bold tracking-tight">Riwayat Transaksi</h1>
                    <form class="flex w-full gap-2 sm:w-auto" action="{{ route('transaksi.index') }}" method="GET">
                        <label class="sr-only" for="transaction-search">Cari</label>
                        <input class="h-11 min-w-0 flex-1 rounded-card border border-brand-lineart bg-transparent px-4 text-base placeholder:text-brand-muted focus:outline-2 focus:outline-brand-primary sm:w-56" id="transaction-search" name="search" type="search" value="{{ $filters['search'] }}" placeholder="Cari">
                        <button class="min-h-11 rounded-card bg-brand-primary px-5 font-bold text-white" type="submit">Cari</button>
                    </form>
                </div>

                <section class="mb-6 rounded-card bg-brand-primary p-5 text-white sm:p-8" aria-label="Urutkan riwayat transaksi">
                    <form class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" action="{{ route('transaksi.index') }}" method="GET">
                        @if ($filters['search'] !== '')
                            <input type="hidden" name="search" value="{{ $filters['search'] }}">
                        @endif
                        <label class="flex min-h-11 items-center gap-3"><input class="size-6 accent-brand-surface" type="radio" name="sort" value="date_desc" @checked($filters['sort'] === 'date_desc')> Tanggal terbaru</label>
                        <label class="flex min-h-11 items-center gap-3"><input class="size-6 accent-brand-surface" type="radio" name="sort" value="date_asc" @checked($filters['sort'] === 'date_asc')> Tanggal terlama</label>
                        <label class="flex min-h-11 items-center gap-3"><input class="size-6 accent-brand-surface" type="radio" name="sort" value="quantity_desc" @checked($filters['sort'] === 'quantity_desc')> Stok terbanyak</label>
                        <label class="flex min-h-11 items-center gap-3"><input class="size-6 accent-brand-surface" type="radio" name="sort" value="quantity_asc" @checked($filters['sort'] === 'quantity_asc')> Stok tersedikit</label>
                        <div class="flex gap-3 sm:col-span-2 lg:col-span-4">
                            <button class="min-h-11 rounded-card bg-brand-surface px-6 font-bold text-brand-ink" type="submit">Terapkan</button>
                            <a class="inline-flex min-h-11 items-center px-3 text-white underline underline-offset-4" href="{{ route('transaksi.index') }}">Reset</a>
                        </div>
                    </form>
                </section>

                <div class="overflow-x-auto rounded-card">
                    <table class="w-full min-w-[700px] border-separate border-spacing-0 text-left">
                        <thead class="bg-brand-primary text-lg font-bold text-brand-ink">
                            <tr>
                                <th class="rounded-l px-4 py-4">No</th>
                                <th class="px-4 py-4">Nama Produk</th>
                                <th class="px-4 py-4">Stok Terjual</th>
                                <th class="px-4 py-4">Tanggal</th>
                                <th class="rounded-r px-4 py-4">Invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $transaction)
                                <tr class="[&:nth-child(even)]:bg-brand-zebra">
                                    <td class="px-4 py-4">{{ $transactions->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-4 font-medium">{{ $transaction->product->name }}</td>
                                    <td class="px-4 py-4">{{ number_format($transaction->quantity_sold, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4">{{ $transaction->sold_at->format('j/n/Y') }}</td>
                                    <td class="px-4 py-4">
                                        @if ($transaction->invoice_path)
                                            <a class="inline-flex min-h-11 items-center text-brand-ink underline underline-offset-4" href="{{ Storage::disk('public')->url($transaction->invoice_path) }}" target="_blank" rel="noopener">Lihat invoice</a>
                                        @else
                                            <span class="text-brand-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $transactions->links() }}</div>
            </section>
        @endif
    </div>
@endsection
