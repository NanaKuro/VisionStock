@extends('layouts.app', ['title' => 'Data Stok'])

@section('content')
    <div class="mx-auto w-full max-w-[1200px]">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-[clamp(1.75rem,3.4vw,3rem)] font-bold tracking-tight">Data Stok</h1>
            </div>
            <button
                class="inline-flex min-h-11 items-center rounded-card bg-brand-primary px-5 py-3 text-base font-bold text-white transition hover:brightness-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink"
                type="button"
                data-product-create
            >
                Input Manual
            </button>
        </div>

        @if (session('status'))
            <p class="mt-6 rounded-xl border border-emerald-800/20 bg-emerald-100 px-4 py-3 text-sm font-medium text-emerald-900" role="status">
                {{ session('status') }}
            </p>
        @endif

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-red-800/20 bg-red-100 px-4 py-3 text-sm font-medium text-red-900" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="mt-8 rounded-card bg-brand-primary p-5 text-white shadow-sm sm:p-8 lg:p-8 xl:px-16" aria-labelledby="filter-heading">
            <h2 id="filter-heading" class="text-2xl font-bold">Filter</h2>
            <form class="mt-5 grid gap-6 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto]" action="{{ route('stok.index') }}" method="GET">
                <fieldset>
                    <legend class="mb-3 text-base font-bold text-white">Berdasarkan Abjad</legend>
                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-base">
                        <label class="inline-flex min-h-11 items-center gap-3">
                            <input class="size-6 accent-brand-surface" type="radio" name="sort" value="name_desc" @checked($filters['sort'] === 'name_desc')>
                            Abjad Z–A
                        </label>
                        <label class="inline-flex min-h-11 items-center gap-3">
                            <input class="size-6 accent-brand-surface" type="radio" name="sort" value="name_asc" @checked($filters['sort'] === 'name_asc')>
                            Abjad A–Z
                        </label>
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="mb-3 text-base font-bold text-white">Berdasarkan Stok</legend>
                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-base">
                        <label class="inline-flex min-h-11 items-center gap-3">
                            <input class="size-6 accent-brand-surface" type="radio" name="sort" value="stock_desc" @checked($filters['sort'] === 'stock_desc')>
                            Stok terbanyak
                        </label>
                        <label class="inline-flex min-h-11 items-center gap-3">
                            <input class="size-6 accent-brand-surface" type="radio" name="sort" value="stock_asc" @checked($filters['sort'] === 'stock_asc')>
                            Stok tersedikit
                        </label>
                    </div>
                </fieldset>

                <div>
                    <label class="mb-3 block text-base font-bold text-white" for="category-filter">Berdasarkan Jenis Produk</label>
                    <select
                        class="h-[55px] w-full rounded border border-brand-surface bg-brand-surface px-4 text-base text-brand-ink outline-none focus:ring-2 focus:ring-white/50"
                        id="category-filter"
                        name="category_id"
                    >
                        <option class="text-brand-ink" value="">Jenis Produk</option>
                        @foreach ($categories as $category)
                            <option class="text-brand-ink" value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2 md:col-span-2 xl:col-span-1">
                    <button class="min-h-11 w-full rounded-card bg-brand-surface px-5 py-3 text-base font-bold text-brand-ink transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" type="submit">
                        Cari
                    </button>
                    <a class="inline-flex min-h-11 items-center rounded-card px-3 py-3 text-sm font-medium text-white underline decoration-white/50 underline-offset-4 hover:text-white" href="{{ route('stok.index') }}">Reset</a>
                </div>
            </form>
        </section>

        @if ($products->isEmpty())
            <section class="mt-8 rounded-card bg-white/35 px-4 py-12 text-center sm:px-8 sm:py-16" aria-labelledby="empty-heading">
                <div class="mx-auto flex size-[clamp(7.5rem,20vw,16.5rem)] items-center justify-center rounded-full border-[5px] border-brand-danger text-[clamp(5rem,12vw,12rem)] font-light leading-none text-brand-danger" aria-hidden="true">×</div>
                <h2 id="empty-heading" class="mt-6 text-[clamp(1.75rem,3.4vw,3rem)] font-bold">
                    {{ $filters['search'] !== '' || $filters['category_id'] !== '' ? 'Produk tidak ditemukan' : 'Data stok belum ada nih' }}
                </h2>
                <p class="mx-auto mt-4 max-w-[570px] text-lg leading-relaxed text-brand-ink/80 sm:text-xl">
                    {{ $filters['search'] !== '' || $filters['category_id'] !== '' ? 'Coba ubah kata pencarian atau filter yang dipilih.' : 'Ayo lakukan input stok atau impor dari pencatatan sebelumnya.' }}
                </p>
                @if ($filters['search'] === '' && $filters['category_id'] === '')
                    <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                        <button
                            class="min-h-11 rounded-card bg-brand-primary px-6 py-3 font-bold text-white transition hover:brightness-95"
                            type="button"
                            data-product-create
                        >
                            Input Manual
                        </button>
                        <a class="inline-flex min-h-11 items-center justify-center rounded-card border-2 border-brand-primary px-6 py-3 font-bold text-brand-ink transition hover:bg-white/30" href="{{ route('stok.import') }}">Impor Excel</a>
                    </div>
                @endif
            </section>
        @else
            <section class="mt-8" aria-label="Daftar produk">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($products as $product)
                        <article class="min-w-0">
                            @if ($product->image_path)
                                <img class="aspect-[264/204] w-full rounded-t-card object-cover" src="{{ Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}">
                            @else
                                <div class="flex aspect-[264/204] items-center justify-center rounded-t-card bg-white/55 text-brand-ink/45" aria-hidden="true">
                                    <svg class="size-16" viewBox="0 0 64 64" fill="none">
                                        <path d="m32 8 22 12v24L32 56 10 44V20L32 8Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                                        <path d="m11 20 21 12 21-12M32 32v24" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            @endif

                            <div class="min-w-0 rounded-b-card bg-brand-primary px-4 py-3 text-white">
                                <h2 class="truncate text-lg font-bold">{{ $product->name }}</h2>
                            </div>
                            <p class="my-3 text-sm text-brand-ink">
                                {{ $product->category?->name ?? 'Jenis belum dipilih' }} · Stok {{ number_format($product->stock, 0, ',', '.') }}
                            </p>
                                <button
                                    class="min-h-11 w-full rounded-card bg-brand-primary px-4 py-3 text-base font-bold text-white transition hover:brightness-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink sm:w-auto sm:min-w-[9.0625rem]"
                                    type="button"
                                    data-product-edit
                                    data-product-id="{{ $product->id }}"
                                    data-update-url="{{ route('stok.update', $product->id) }}"
                                    data-name="{{ $product->name }}"
                                    data-description="{{ $product->description }}"
                                    data-stock="{{ $product->stock }}"
                                    data-category-name="{{ $product->category?->name }}"
                                    data-image="{{ $product->image_path ? Storage::disk('public')->url($product->image_path) : '' }}"
                                >
                                    Edit Stok
                                </button>
                        </article>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            </section>
        @endif
    </div>

    <dialog
        class="m-auto max-h-[90dvh] w-[min(92vw,1073px)] overflow-y-auto rounded-card bg-brand-surface p-0 text-brand-ink shadow-2xl backdrop:bg-black/45"
        aria-labelledby="product-dialog-heading"
        data-product-dialog
        data-store-url="{{ route('stok.store') }}"
        data-auto-open="{{ old('product_form') ? 'true' : 'false' }}"
        data-auto-update-url="{{ old('product_id') ? route('stok.update', old('product_id')) : '' }}"
        data-auto-method="{{ old('_method', 'POST') }}"
    >
        <form class="p-4 sm:p-8 lg:p-16" action="{{ route('stok.store') }}" method="POST" enctype="multipart/form-data" data-product-form>
            @csrf
            <input type="hidden" name="_method" value="POST" data-product-method>
            <input type="hidden" name="product_form" value="1">
            <input type="hidden" name="product_id" value="{{ old('product_id') }}">

            <div class="mb-7 flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-brand-ink/60">Data Stok</p>
                    <h2 class="mt-1 text-2xl font-semibold" id="product-dialog-heading" data-dialog-title>Input Manual</h2>
                </div>
                <button class="rounded-lg p-2 text-2xl leading-none hover:bg-white/40 focus-visible:outline-2 focus-visible:outline-brand-ink" type="button" aria-label="Tutup" data-product-close>×</button>
            </div>

            <div class="mb-6 flex flex-col items-start gap-5 sm:flex-row sm:items-center">
                <div class="flex aspect-[342/264] w-full max-w-[342px] items-center justify-center overflow-hidden rounded-card bg-white/75">
                    <img class="hidden size-full object-cover" alt="" data-product-preview>
                    <svg class="size-12 text-brand-ink/40" viewBox="0 0 64 64" fill="none" data-product-placeholder aria-hidden="true">
                        <path d="m32 8 22 12v24L32 56 10 44V20L32 8Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                        <path d="m11 20 21 12 21-12M32 32v24" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>

            <div class="grid gap-6">
                <div>
                    <label class="mb-3 block text-lg font-bold" for="product-name">Nama Produk</label>
                    <input class="h-[55px] w-full rounded-card border border-brand-lineart bg-white/35 px-6 text-base outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="product-name" name="name" type="text" value="{{ old('name') }}" maxlength="255" required>
                    @error('name')
                        <p class="mt-1 text-sm font-medium text-brand-danger" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-6 md:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
                    <div>
                        <label class="mb-3 block text-lg font-bold" for="product-description">Deskripsi Produk</label>
                        <textarea class="min-h-[100px] w-full rounded-card border border-brand-lineart bg-white/35 px-6 py-4 text-base outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="product-description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm font-medium text-brand-danger" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-3 block text-lg font-bold" for="product-image">Gambar Produk</label>
                        <input class="block w-full text-sm file:mr-3 file:min-h-11 file:rounded-card file:border-0 file:bg-brand-primary file:px-4 file:py-2 file:font-bold file:text-white" id="product-image" name="image" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-product-image>
                        <p class="mt-2 text-sm text-brand-muted">Format JPG atau PNG, maksimal 5 MB.</p>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-3 block text-lg font-bold" for="product-stock">Stok Produk</label>
                        <input class="h-[55px] w-full rounded-card border border-brand-lineart bg-white/35 px-6 text-base outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="product-stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', 0) }}" required>
                        @error('stock')
                            <p class="mt-1 text-sm font-medium text-brand-danger" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-3 block text-lg font-bold" for="product-category">Jenis Produk</label>
                        <select class="h-[55px] w-full rounded border border-brand-lineart bg-white/35 px-6 text-base outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="product-category" name="category_name">
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
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end sm:gap-6">
                <button class="min-h-11 rounded-card border border-brand-lineart/30 px-6 py-3 font-bold transition hover:bg-white/35 focus-visible:outline-2 focus-visible:outline-brand-ink" type="button" data-product-close>Batal</button>
                <button class="min-h-11 rounded-card bg-brand-primary px-7 py-3 font-bold text-white transition hover:brightness-95 focus-visible:outline-2 focus-visible:outline-brand-ink" type="submit">Simpan</button>
            </div>
        </form>
    </dialog>
@endsection
