@extends('layouts.app', ['title' => 'Impor Data'])

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-[0.16em] text-brand-ink/65">Data Stok</p>
                <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Impor Data</h1>
            </div>
            <a class="rounded-xl border border-brand-ink/25 px-4 py-2.5 text-sm font-semibold transition hover:bg-white/35" href="{{ route('stok.index') }}">Kembali ke Data Stok</a>
        </div>

        @if (session('status'))
            <p class="mt-6 rounded-xl border border-emerald-800/20 bg-emerald-100 px-4 py-3 text-sm font-medium text-emerald-900" role="status">
                {{ session('status') }}
            </p>
        @endif

        @if ($errors->has('file'))
            <p class="mt-6 rounded-xl border border-red-800/20 bg-red-100 px-4 py-3 text-sm font-medium text-red-900" role="alert">
                {{ $errors->first('file') }}
            </p>
        @endif

        @if (session('stock_import_result'))
            @php($importResult = session('stock_import_result'))
            @if ($importResult['failures'] !== [])
                <section class="mt-6 rounded-card border border-amber-800/15 bg-amber-50 p-5 text-amber-950" aria-labelledby="import-errors-heading">
                    <h2 class="text-lg font-semibold" id="import-errors-heading">Baris yang perlu diperbaiki</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($importResult['failures'] as $failure)
                            <li>
                                <span class="font-semibold">Baris {{ $failure['row'] }}:</span>
                                {{ implode(' ', $failure['messages']) }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @endif

        <section class="mt-8" aria-labelledby="excel-heading">
            <h2 class="text-2xl font-semibold" id="excel-heading">Unggah Excel</h2>
            <form class="mt-4 rounded-card bg-brand-primary p-5 text-white shadow-sm sm:p-8" action="{{ route('stok.import.excel') }}" method="POST" enctype="multipart/form-data" data-stock-import-form>
                @csrf
                <div class="flex min-h-56 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-white/50 px-5 py-8 text-center transition" data-dropzone>
                    <label class="inline-flex cursor-pointer items-center gap-3 rounded-2xl bg-brand-surface px-6 py-4 font-semibold text-brand-ink shadow-sm transition hover:bg-white focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-white" for="stock-import-file">
                        <svg aria-hidden="true" class="size-7" viewBox="0 0 24 24" fill="none">
                            <path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14v5h14v-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Unggah
                    </label>
                    <input class="sr-only" id="stock-import-file" name="file" type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required data-excel-file>
                    <p class="mt-6 text-sm text-white/90" data-file-name>Pilih sebuah file atau tahan dan lepaskan di sini.</p>
                    <p class="mt-3 text-sm font-medium">Maksimum ukuran 10 MB · Format .xlsx</p>
                    <p class="mt-2 text-sm text-amber-100" role="alert" data-upload-error></p>
                </div>

                <div class="mt-5 hidden rounded-xl bg-white/15 p-4" role="status" aria-live="polite" data-upload-progress>
                    <p class="font-semibold">File sedang diunggah… <span data-progress-percent>90%</span></p>
                    <progress class="mt-2 h-2 w-full accent-brand-surface" max="100" value="90">90%</progress>
                </div>

                <div class="mt-5 flex flex-wrap items-center justify-between gap-4">
                    <a class="text-sm font-semibold text-white underline decoration-white/60 underline-offset-4 hover:text-white/80" href="{{ route('stok.template') }}">
                        Unduh template Excel
                    </a>
                    <button class="rounded-xl bg-brand-surface px-6 py-3 font-semibold text-brand-ink transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" type="submit" data-upload-submit>
                        Impor Data
                    </button>
                </div>
            </form>
        </section>

        <section class="mt-10 rounded-card bg-white/75 p-5 shadow-sm sm:p-8" aria-labelledby="manual-heading">
            <h2 class="text-2xl font-semibold" id="manual-heading">Impor Manual</h2>
            <form class="mt-6 grid gap-5 sm:grid-cols-2" action="{{ route('stok.import.manual') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="sm:col-span-2">
                    <label class="mb-2 block font-semibold" for="manual-product-name">Nama Produk</label>
                    <input class="w-full rounded-xl border border-brand-ink/40 bg-transparent px-4 py-3 outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="manual-product-name" name="name" type="text" value="{{ old('name') }}" maxlength="255" required>
                    @error('name')
                        <p class="mt-1 text-sm font-medium text-red-800" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-2 block font-semibold" for="manual-product-description">Deskripsi Produk</label>
                    <textarea class="w-full rounded-xl border border-brand-ink/40 bg-transparent px-4 py-3 outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="manual-product-description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm font-medium text-red-800" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-semibold" for="manual-product-image">Gambar Produk</label>
                    <input class="block w-full text-sm file:mr-3 file:rounded-xl file:border-0 file:bg-brand-primary file:px-4 file:py-2 file:font-semibold file:text-white" id="manual-product-image" name="image" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                    <p class="mt-2 text-xs text-brand-ink/65">JPG atau PNG, maksimal 5 MB.</p>
                    @error('image')
                        <p class="mt-1 text-sm font-medium text-red-800" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-semibold" for="manual-product-stock">Stok Produk</label>
                    <input class="w-full rounded-xl border border-brand-ink/40 bg-transparent px-4 py-3 outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="manual-product-stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', 0) }}" required>
                    @error('stock')
                        <p class="mt-1 text-sm font-medium text-red-800" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-2 block font-semibold" for="manual-product-category">Jenis Produk</label>
                    <input class="w-full rounded-xl border border-brand-ink/40 bg-transparent px-4 py-3 outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="manual-product-category" name="category_name" type="text" value="{{ old('category_name') }}" maxlength="255">
                    @error('category_name')
                        <p class="mt-1 text-sm font-medium text-red-800" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2 flex flex-wrap justify-end gap-3 pt-2">
                    <button class="rounded-xl border border-brand-ink/30 px-5 py-3 font-semibold transition hover:bg-brand-surface/50" type="reset">Reset</button>
                    <button class="rounded-xl bg-brand-primary px-6 py-3 font-semibold text-white transition hover:brightness-95" type="submit">Simpan</button>
                </div>
            </form>
        </section>
    </div>
@endsection
