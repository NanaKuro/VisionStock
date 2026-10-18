@extends('layouts.app', ['title' => 'Data Stok'])

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-[0.16em] text-brand-ink/65">VisionStock</p>
                <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Data Stok</h1>
            </div>
            <button
                class="rounded-full bg-brand-primary px-5 py-3 font-semibold text-white transition hover:brightness-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink"
                type="button"
                data-product-create
            >
                Input Manual
            </button>
            <a
                class="rounded-full border-2 border-brand-primary px-5 py-3 font-semibold text-brand-ink transition hover:bg-white/30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink"
                href="{{ route('stok.import') }}"
            >
                Impor Stok
            </a>
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

        <section class="mt-8 rounded-card bg-brand-primary p-5 text-white shadow-sm sm:p-7" aria-labelledby="filter-heading">
            <h2 id="filter-heading" class="text-2xl font-semibold">Filter</h2>
            <form class="mt-5 grid gap-5 xl:grid-cols-2 2xl:grid-cols-[minmax(220px,1fr)_minmax(250px,1fr)_minmax(180px,0.8fr)_minmax(220px,1fr)_auto]" action="{{ route('stok.index') }}" method="GET">
                <div class="xl:col-span-1">
                    <label class="mb-2 block text-sm font-semibold text-white/90" for="product-search">Cari produk</label>
                    <input
                        class="w-full rounded-xl border border-white/45 bg-white/15 px-4 py-3 text-sm text-white outline-none placeholder:text-white/70 focus:border-white focus:ring-2 focus:ring-white/35"
                        id="product-search"
                        name="search"
                        type="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Cari nama produk"
                    >
                </div>

                <fieldset>
                    <legend class="mb-2 text-sm font-semibold text-white/90">Berdasarkan Abjad</legend>
                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm">
                        <label class="inline-flex items-center gap-2">
                            <input class="size-4 accent-brand-ink" type="radio" name="sort" value="name_desc" @checked($filters['sort'] === 'name_desc')>
                            Abjad Z–A
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input class="size-4 accent-brand-ink" type="radio" name="sort" value="name_asc" @checked($filters['sort'] === 'name_asc')>
                            Abjad A–Z
                        </label>
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="mb-2 text-sm font-semibold text-white/90">Berdasarkan Stok</legend>
                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm">
                        <label class="inline-flex items-center gap-2">
                            <input class="size-4 accent-brand-ink" type="radio" name="sort" value="stock_desc" @checked($filters['sort'] === 'stock_desc')>
                            Stok terbanyak
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input class="size-4 accent-brand-ink" type="radio" name="sort" value="stock_asc" @checked($filters['sort'] === 'stock_asc')>
                            Stok tersedikit
                        </label>
                    </div>
                </fieldset>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-white/90" for="category-filter">Jenis Produk</label>
                    <select
                        class="w-full rounded-xl border border-white/45 bg-white/15 px-4 py-3 text-sm text-white outline-none focus:border-white focus:ring-2 focus:ring-white/35"
                        id="category-filter"
                        name="category_id"
                    >
                        <option class="text-brand-ink" value="">Semua jenis</option>
                        @foreach ($categories as $category)
                            <option class="text-brand-ink" value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button class="w-full rounded-xl bg-brand-surface px-5 py-3 text-sm font-semibold text-brand-ink transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" type="submit">
                        Terapkan
                    </button>
                    <a class="rounded-xl px-3 py-3 text-sm font-medium text-white/90 underline decoration-white/50 underline-offset-4 hover:text-white" href="{{ route('stok.index') }}">Reset</a>
                </div>
            </form>
        </section>

        @if ($products->isEmpty())
            <section class="mt-8 rounded-card bg-white/70 px-6 py-14 text-center shadow-sm sm:py-20" aria-labelledby="empty-heading">
                <div class="mx-auto flex size-20 items-center justify-center rounded-full border-2 border-red-400 text-5xl font-light text-red-500" aria-hidden="true">×</div>
                <h2 id="empty-heading" class="mt-6 text-2xl font-semibold">
                    {{ $filters['search'] !== '' || $filters['category_id'] !== '' ? 'Produk tidak ditemukan' : 'Data stok belum ada' }}
                </h2>
                <p class="mx-auto mt-3 max-w-lg text-brand-ink/70">
                    {{ $filters['search'] !== '' || $filters['category_id'] !== '' ? 'Coba ubah kata pencarian atau filter yang dipilih.' : 'Tambahkan produk secara manual untuk mulai mencatat persediaan.' }}
                </p>
                @if ($filters['search'] === '' && $filters['category_id'] === '')
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <button
                            class="rounded-full bg-brand-primary px-6 py-3 font-semibold text-white transition hover:brightness-95"
                            type="button"
                            data-product-create
                        >
                            Input Manual
                        </button>
                        <a class="rounded-full border-2 border-brand-primary px-6 py-3 font-semibold text-brand-ink transition hover:bg-white/30" href="{{ route('stok.import') }}">Impor Excel</a>
                    </div>
                @endif
            </section>
        @else
            <section class="mt-8" aria-label="Daftar produk">
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($products as $product)
                        <article class="overflow-hidden rounded-card bg-white shadow-sm">
                            @if ($product->image_path)
                                <img class="aspect-[4/3] w-full object-cover" src="{{ Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}">
                            @else
                                <div class="flex aspect-[4/3] items-center justify-center bg-brand-surface/60 text-brand-ink/45" aria-hidden="true">
                                    <svg class="size-16" viewBox="0 0 64 64" fill="none">
                                        <path d="m32 8 22 12v24L32 56 10 44V20L32 8Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                                        <path d="m11 20 21 12 21-12M32 32v24" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            @endif

                            <div class="flex items-center justify-between gap-4 bg-brand-primary px-5 py-4 text-white">
                                <div class="min-w-0">
                                    <h2 class="truncate text-lg font-semibold">{{ $product->name }}</h2>
                                    <p class="mt-1 text-sm text-white/85">
                                        {{ $product->category?->name ?? 'Jenis belum dipilih' }} · Stok {{ number_format($product->stock, 0, ',', '.') }}
                                    </p>
                                </div>
                                <button
                                    class="shrink-0 rounded-xl bg-brand-surface px-4 py-2 text-sm font-semibold text-brand-ink transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                                    type="button"
                                    data-product-edit
                                    data-product-id="{{ $product->id }}"
                                    data-update-url="{{ route('stok.update', $product->id) }}"
                                    data-name="{{ $product->name }}"
                                    data-description="{{ $product->description }}"
                                    data-stock="{{ $product->stock }}"
                                    data-category="{{ $product->category_id }}"
                                    data-image="{{ $product->image_path ? Storage::disk('public')->url($product->image_path) : '' }}"
                                >
                                    Edit Stok
                                </button>
                            </div>
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
        class="m-auto max-h-[90vh] w-[min(900px,calc(100%-2rem))] overflow-y-auto rounded-[1.5rem] bg-brand-surface p-0 text-brand-ink shadow-2xl backdrop:bg-brand-ink/60"
        aria-labelledby="product-dialog-heading"
        data-product-dialog
        data-store-url="{{ route('stok.store') }}"
        data-auto-open="{{ old('product_form') ? 'true' : 'false' }}"
        data-auto-update-url="{{ old('product_id') ? route('stok.update', old('product_id')) : '' }}"
        data-auto-method="{{ old('_method', 'POST') }}"
    >
        <form class="p-6 sm:p-9" action="{{ route('stok.store') }}" method="POST" enctype="multipart/form-data" data-product-form>
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

            <div class="mb-6 flex flex-wrap items-center gap-5">
                <div class="flex size-32 items-center justify-center overflow-hidden rounded-2xl bg-white/75">
                    <img class="hidden size-full object-cover" alt="" data-product-preview>
                    <svg class="size-12 text-brand-ink/40" viewBox="0 0 64 64" fill="none" data-product-placeholder aria-hidden="true">
                        <path d="m32 8 22 12v24L32 56 10 44V20L32 8Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                        <path d="m11 20 21 12 21-12M32 32v24" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <label class="block text-sm font-semibold" for="product-image">Gambar Produk</label>
                    <input class="mt-2 block max-w-full text-sm file:mr-3 file:rounded-xl file:border-0 file:bg-brand-primary file:px-4 file:py-2 file:font-semibold file:text-white" id="product-image" name="image" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-product-image>
                    <p class="mt-2 text-xs text-brand-ink/65">Format JPG atau PNG, maksimal 5 MB.</p>
                    @error('image')
                        <p class="mt-1 text-sm font-medium text-red-800" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-2 block font-semibold" for="product-name">Nama Produk</label>
                    <input class="w-full rounded-xl border border-brand-ink/50 bg-white/35 px-4 py-3 outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="product-name" name="name" type="text" value="{{ old('name') }}" maxlength="255" required>
                    @error('name')
                        <p class="mt-1 text-sm font-medium text-red-800" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-2 block font-semibold" for="product-description">Deskripsi Produk</label>
                    <textarea class="w-full rounded-xl border border-brand-ink/50 bg-white/35 px-4 py-3 outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="product-description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm font-medium text-red-800" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-semibold" for="product-category">Jenis Produk</label>
                    <select class="w-full rounded-xl border border-brand-ink/50 bg-white/35 px-4 py-3 outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="product-category" name="category_id">
                        <option value="">Pilih jenis produk</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm font-medium text-red-800" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-semibold" for="product-stock">Stok Produk</label>
                    <input class="w-full rounded-xl border border-brand-ink/50 bg-white/35 px-4 py-3 outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="product-stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', 0) }}" required>
                    @error('stock')
                        <p class="mt-1 text-sm font-medium text-red-800" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex flex-wrap justify-end gap-3">
                <button class="rounded-xl border border-brand-ink/30 px-5 py-3 font-semibold transition hover:bg-white/35 focus-visible:outline-2 focus-visible:outline-brand-ink" type="button" data-product-close>Batal</button>
                <button class="rounded-xl bg-brand-primary px-6 py-3 font-semibold text-white transition hover:brightness-95 focus-visible:outline-2 focus-visible:outline-brand-ink" type="submit">Simpan</button>
            </div>
        </form>
    </dialog>
@endsection
