@extends('layouts.app', ['title' => 'Impor Data'])

@section('content')
    <div class="mx-auto w-full max-w-[1200px]">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-[clamp(1.75rem,3.4vw,3rem)] font-bold tracking-tight">Impor Data</h1>
            </div>
            <a class="inline-flex min-h-11 items-center rounded-card bg-brand-primary px-5 py-3 text-base font-bold text-white transition hover:brightness-95" href="{{ route('stok.index') }}">Kembali ke Data Stok</a>
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
            <h2 class="text-2xl font-bold" id="excel-heading">Unggah Excel</h2>
            <form class="mt-4 w-full rounded-card bg-brand-primary p-4 text-white shadow-sm sm:p-8" action="{{ route('stok.import.excel') }}" method="POST" enctype="multipart/form-data" data-stock-import-form>
                @csrf
                <div class="flex min-h-[18.25rem] flex-col items-center justify-center rounded-card border-2 border-dashed border-white/50 px-4 py-8 text-center transition" data-dropzone>
                    <label class="inline-flex min-h-[4.5rem] w-full max-w-[12.625rem] cursor-pointer items-center justify-center gap-3 rounded-card bg-brand-surface px-6 py-4 text-lg font-bold text-brand-ink shadow-sm transition hover:bg-white focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-white" for="stock-import-file">
                        <svg aria-hidden="true" class="size-10" viewBox="0 0 24 24" fill="none">
                            <path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14v5h14v-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Unggah
                    </label>
                    <input class="sr-only" id="stock-import-file" name="file" type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required data-excel-file>
                    <p class="mt-6 max-w-[40rem] break-words text-base leading-relaxed text-white sm:text-xl" data-file-name>Pilihlah sebuah file atau tahan dan lepaskan disini</p>
                    <p class="mt-3 text-base font-medium text-white sm:text-xl">Maksimum ukuran 10 MB · Format .xlsx</p>
                    <p class="mt-2 text-sm text-amber-100" role="alert" data-upload-error></p>
                </div>

                <div class="mt-5 hidden rounded-xl bg-white/15 p-4" role="status" aria-live="polite" data-upload-progress>
                    <p class="font-semibold">File sedang diunggah… <span data-progress-percent>90%</span></p>
                    <progress class="mt-2 h-2 w-full accent-brand-surface" max="100" value="90">90%</progress>
                </div>

                <div class="mt-5 flex flex-col-reverse items-stretch justify-between gap-4 sm:flex-row sm:items-center">
                    <a class="inline-flex min-h-11 items-center text-sm font-semibold text-white underline decoration-white/60 underline-offset-4 hover:text-white/80" href="{{ route('stok.template') }}">
                        Unduh template Excel
                    </a>
                    <button class="min-h-11 rounded-card bg-brand-surface px-6 py-3 font-bold text-brand-ink transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" type="submit" data-upload-submit>
                        Impor Data
                    </button>
                </div>
            </form>
        </section>

        <section class="mt-10" aria-labelledby="manual-heading">
            <h2 class="text-2xl font-bold" id="manual-heading">Impor Manual</h2>
            <form class="mt-6 grid gap-6" action="{{ route('stok.import.manual') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div>
                    <label class="mb-3 block text-lg font-bold" for="manual-product-name">Nama Produk</label>
                    <input class="h-[55px] w-full rounded-card border border-brand-lineart bg-transparent px-6 text-base outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="manual-product-name" name="name" type="text" value="{{ old('name') }}" maxlength="255" required>
                    @error('name')
                        <p class="mt-1 text-sm font-medium text-brand-danger" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-6 md:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
                    <div>
                        <label class="mb-3 block text-lg font-bold" for="manual-product-description">Deskripsi Produk</label>
                        <textarea class="min-h-[100px] w-full rounded-card border border-brand-lineart bg-transparent px-6 py-4 text-base outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="manual-product-description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm font-medium text-brand-danger" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-3 block text-lg font-bold" for="manual-product-image">Gambar Produk</label>
                        <input class="block w-full text-sm file:mr-3 file:min-h-11 file:rounded-card file:border-0 file:bg-brand-primary file:px-4 file:py-2 file:font-bold file:text-white" id="manual-product-image" name="image" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                        <p class="mt-2 text-sm text-brand-muted">JPG atau PNG, maksimal 5 MB.</p>
                        @error('image')
                            <p class="mt-1 text-sm font-medium text-brand-danger" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-3 block text-lg font-bold" for="manual-product-stock">Stok Produk</label>
                        <input class="h-[55px] w-full rounded-card border border-brand-lineart bg-transparent px-6 text-base outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="manual-product-stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', 0) }}" required>
                        @error('stock')
                            <p class="mt-1 text-sm font-medium text-brand-danger" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-3 block text-lg font-bold" for="manual-product-category">Jenis Produk</label>
                        <select class="h-[55px] w-full rounded-card border border-brand-lineart bg-transparent px-6 text-base outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="manual-product-category" name="category_name">
                            <option value="">Pilih jenis produk</option>
                            @foreach ($categoryNames as $categoryName)
                                <option value="{{ $categoryName }}" @selected(old('category_name') === $categoryName)>{{ $categoryName }}</option>
                            @endforeach
                        </select>
                        @error('category_name')
                            <p class="mt-1 text-sm font-medium text-brand-danger" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end sm:gap-6">
                    <button class="min-h-11 rounded-card border border-brand-lineart/30 px-6 py-3 font-bold transition hover:bg-brand-surface/50" type="reset">Reset</button>
                    <button class="min-h-11 rounded-card bg-brand-primary px-7 py-3 font-bold text-white transition hover:brightness-95" type="submit">Simpan</button>
                </div>
            </form>
        </section>
    </div>
@endsection
