import '../css/app.css';

const app = document.querySelector('#app');
const path = window.location.pathname.replace(/\/+$/, '') || '/';
const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
})[character]);
const number = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));
const date = (value) => new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value));

async function api(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers: {
            ...(options.body instanceof FormData ? {} : { 'Content-Type': 'application/json' }),
            ...options.headers,
        },
    });
    const data = response.headers.get('content-type')?.includes('application/json')
        ? await response.json()
        : null;
    if (!response.ok) {
        const error = new Error(data?.error || `Request gagal (${response.status}).`);
        error.status = response.status;
        throw error;
    }
    return data;
}

const link = (href, label, active = false) => `<a class="rounded-xl px-4 py-3 font-semibold transition ${active ? 'bg-white/25 text-white' : 'text-white/90 hover:bg-white/15'}" href="${href}">${label}</a>`;
const button = (label, type = 'submit', extra = '') => `<button class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-primary px-5 py-3 font-bold text-white transition hover:brightness-95 disabled:opacity-60 ${extra}" type="${type}">${label}</button>`;
const field = (label, name, value = '', type = 'text', attrs = '') => `
    <label class="block">
        <span class="mb-2 block font-semibold">${label}</span>
        <input class="min-h-12 w-full rounded-xl border border-brand-lineart/30 bg-white px-4 py-3 outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" name="${name}" type="${type}" value="${escapeHtml(value)}" ${attrs}>
    </label>`;
const panel = (content, extra = '') => `<section class="rounded-2xl bg-white p-5 shadow-sm sm:p-7 ${extra}">${content}</section>`;
const table = (headers, rows) => `
    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm">
        <table class="w-full min-w-[640px] border-collapse text-left">
            <thead class="bg-brand-primary text-brand-ink"><tr>${headers.map((header) => `<th class="px-4 py-4 font-bold">${header}</th>`).join('')}</tr></thead>
            <tbody>${rows || `<tr><td class="px-4 py-8 text-center text-brand-muted" colspan="${headers.length}">Belum ada data.</td></tr>`}</tbody>
        </table>
    </div>`;
const row = (cells) => `<tr class="border-b border-slate-100 last:border-0 even:bg-slate-50">${cells.map((cell) => `<td class="px-4 py-4 align-middle">${cell}</td>`).join('')}</tr>`;

function layout(user, title, content) {
    const nav = [
        ['/dashboard', 'Dashboard'],
        ['/stok', 'Data Stok'],
        ['/analisis', 'Analisis Stok'],
        ['/transaksi', 'Riwayat Transaksi'],
        ['/pengaturan', 'Pengaturan'],
    ];
    document.title = `${title} | VisionStock`;
    return `
        <header class="flex min-h-20 flex-wrap items-center justify-between gap-4 bg-brand-primary px-5 py-4 text-white shadow sm:px-8">
            <a class="text-2xl font-extrabold tracking-tight" href="/dashboard">VisionStock</a>
            <div class="flex items-center gap-4"><span class="hidden sm:inline">${escapeHtml(user.name)}</span><button class="rounded-xl bg-white/20 px-4 py-2 font-semibold hover:bg-white/30" type="button" data-logout>Keluar</button></div>
        </header>
        <div class="mx-auto grid min-h-[calc(100vh-5rem)] max-w-[1500px] lg:grid-cols-[250px_1fr]">
            <aside class="bg-brand-primary p-4 lg:p-6">
                <h2 class="mb-4 px-4 text-xl font-bold text-white">${title}</h2>
                <nav class="grid grid-cols-2 gap-2 lg:grid-cols-1" aria-label="Navigasi utama">${nav.map(([href, label]) => link(href, label, path === href || (href !== '/dashboard' && path.startsWith(href)))).join('')}</nav>
            </aside>
            <main class="min-w-0 p-4 sm:p-8 lg:p-10">
                <div id="notice" class="mb-5 hidden rounded-xl border px-4 py-3" role="status"></div>
                ${content}
            </main>
        </div>`;
}

function guestPage() {
    const login = path === '/login';
    const register = path === '/register';
    if (login || register) {
        return `
            <main class="grid min-h-screen place-items-center p-4">
                <section class="w-full max-w-lg rounded-2xl bg-white p-7 shadow-lg sm:p-10">
                    <a class="text-2xl font-extrabold text-brand-primary" href="/">VisionStock</a>
                    <h1 class="mt-7 text-3xl font-bold">${login ? 'Masuk' : 'Daftar'}</h1>
                    <p class="mt-2 text-brand-muted">${login ? 'Masuk untuk mengelola stok dan transaksi.' : 'Buat akun untuk mulai mengelola stok.'}</p>
                    <form class="mt-7 grid gap-5" data-form="${login ? 'login' : 'register'}">
                        ${field('Username atau email', 'identifier', '', 'text', 'autocomplete="username" required minlength="3"')}
                        ${field('Password', 'password', '', 'password', 'autocomplete="current-password" required minlength="8"')}
                        ${button(login ? 'Masuk' : 'Daftar')}
                    </form>
                    <p class="mt-6 text-sm">${login ? 'Belum punya akun?' : 'Sudah punya akun?'} <a class="font-bold text-brand-ink underline" href="${login ? '/register' : '/login'}">${login ? 'Daftar' : 'Masuk'}</a></p>
                    <p class="mt-4"><a class="text-sm text-brand-muted underline" href="/">Kembali ke beranda</a></p>
                </section>
            </main>`;
    }

    return `
        <div class="min-h-screen">
            <header class="flex min-h-24 items-center justify-between bg-brand-primary px-5 text-white sm:px-12">
                <a class="text-2xl font-extrabold" href="/">VisionStock</a>
                <nav class="flex gap-3"><a class="rounded-xl px-4 py-3 font-bold hover:bg-white/20" href="/login">Masuk</a><a class="rounded-xl bg-white px-4 py-3 font-bold text-brand-ink" href="/register">Daftar</a></nav>
            </header>
            <main class="mx-auto grid min-h-[calc(100vh-6rem)] max-w-6xl items-center gap-10 px-6 py-16 md:grid-cols-2">
                <section><p class="font-bold uppercase tracking-widest text-brand-ink">VisionStock</p><h1 class="mt-4 text-4xl font-extrabold leading-tight sm:text-6xl">Solusi Permasalahan Stok</h1><p class="mt-6 max-w-xl text-lg text-slate-700">Kelola persediaan, catat transaksi, dan lihat rekomendasi pengisian stok dengan mudah.</p><a class="mt-8 inline-flex rounded-xl bg-brand-primary px-7 py-4 font-bold text-white" href="/register">Coba Sekarang</a></section>
                <div class="rounded-3xl bg-white/70 p-10 text-center shadow"><div class="mx-auto grid size-56 place-items-center rounded-full bg-brand-surface text-7xl">📦</div><p class="mt-6 text-lg font-semibold">Stok lebih rapi, keputusan lebih tepat.</p></div>
            </main>
        </div>`;
}

async function renderDashboard(user) {
    const { metrics } = await api('/api/dashboard');
    const items = [
        ['Produk', metrics.products], ['Unit tersedia', metrics.units_in_stock],
        ['Unit terjual', metrics.units_sold], ['Transaksi', metrics.transaction_records],
    ];
    return layout(user, 'Dashboard', `
        <h1 class="text-3xl font-extrabold sm:text-4xl">Ringkasan Persediaan</h1>
        <p class="mt-2 text-brand-muted">Pantau stok dan aktivitas toko Anda.</p>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">${items.map(([label, value]) => panel(`<p class="text-sm font-semibold text-brand-muted">${label}</p><p class="mt-3 text-4xl font-extrabold">${number(value)}</p>`)).join('')}</div>
        <div class="mt-8 flex flex-wrap gap-3"><a class="rounded-xl bg-brand-primary px-5 py-3 font-bold text-white" href="/stok">Kelola Stok</a><a class="rounded-xl border border-brand-primary px-5 py-3 font-bold" href="/analisis">Lihat Analisis</a><a class="rounded-xl border border-brand-primary px-5 py-3 font-bold" href="/transaksi/impor">Input Transaksi</a></div>`);
}

async function renderProducts(user) {
    const params = new URLSearchParams(location.search);
    const query = new URLSearchParams({ search: params.get('search') || '', sort: params.get('sort') || 'name_asc', category_name: params.get('category_name') || '' });
    const { products, categories } = await api(`/api/products?${query}`);
    const options = categories.map((name) => `<option ${params.get('category_name') === name ? 'selected' : ''}>${escapeHtml(name)}</option>`).join('');
    const productRows = products.map((product, index) => row([
        number(index + 1),
        `<div class="font-semibold">${escapeHtml(product.name)}</div>${product.description ? `<div class="mt-1 text-sm text-brand-muted">${escapeHtml(product.description)}</div>` : ''}`,
        escapeHtml(product.category_name || '—'),
        number(product.stock),
        `<div class="flex gap-3"><a class="font-semibold underline" href="/stok/edit/${product.id}">Edit</a></div>`,
    ])).join('');
    return layout(user, 'Data Stok', `
        <div class="flex flex-wrap items-end justify-between gap-4"><div><h1 class="text-3xl font-extrabold sm:text-4xl">Data Stok</h1><p class="mt-2 text-brand-muted">${products.length} produk terdaftar</p></div><div class="flex gap-3"><a class="rounded-xl border border-brand-primary px-4 py-3 font-bold" href="/stok/impor">Impor Excel</a><a class="rounded-xl bg-brand-primary px-4 py-3 font-bold text-white" href="/stok/tambah">Input Manual</a></div></div>
        ${panel(`<form class="grid gap-4 md:grid-cols-4" method="GET"><label class="block"><span class="mb-2 block font-semibold">Cari produk</span><input class="min-h-12 w-full rounded-xl border px-4" name="search" value="${escapeHtml(params.get('search') || '')}" placeholder="Nama produk"></label><label class="block"><span class="mb-2 block font-semibold">Jenis Produk</span><select class="min-h-12 w-full rounded-xl border px-4" name="category_name"><option value="">Semua jenis</option>${options}</select></label><label class="block"><span class="mb-2 block font-semibold">Urutkan</span><select class="min-h-12 w-full rounded-xl border px-4" name="sort">${[['name_asc','Nama A-Z'],['name_desc','Nama Z-A'],['stock_desc','Stok terbanyak'],['stock_asc','Stok tersedikit']].map(([value,label]) => `<option value="${value}" ${(params.get('sort') || 'name_asc') === value ? 'selected' : ''}>${label}</option>`).join('')}</select></label><div class="flex items-end gap-2">${button('Terapkan')}<a class="py-3 underline" href="/stok">Reset</a></div></form>`, 'mb-6')}
        ${table(['No.', 'Nama Produk', 'Jenis Produk', 'Stok', 'Aksi'], productRows)}`);
}

async function renderProductForm(user, editId = null) {
    const { products, categories } = await api('/api/products');
    const product = editId ? products.find((item) => String(item.id) === editId) : null;
    if (editId && !product) throw new Error('Produk tidak ditemukan.');
    return layout(user, editId ? 'Edit Produk' : 'Input Manual', `
        <h1 class="text-3xl font-extrabold">${editId ? 'Edit Produk' : 'Input Stok Manual'}</h1>
        ${panel(`<form class="mt-6 grid max-w-3xl gap-5" data-form="product" data-id="${product?.id || ''}">
            ${field('Nama Produk', 'name', product?.name || '', 'text', 'maxlength="255" required')}
            <label class="block"><span class="mb-2 block font-semibold">Jenis Produk</span><select class="min-h-12 w-full rounded-xl border bg-white px-4" name="category_name"><option value="">Pilih jenis</option>${[...new Set([...categories, 'Alat Tulis','Bahan Baku Makanan','Buku','Minuman','Makanan','Perlengkapan Rumah Tangga','Barang lainnya'])].map((name) => `<option value="${escapeHtml(name)}" ${product?.category_name === name ? 'selected' : ''}>${escapeHtml(name)}</option>`).join('')}</select></label>
            <label class="block"><span class="mb-2 block font-semibold">Deskripsi</span><textarea class="w-full rounded-xl border bg-white px-4 py-3" name="description" rows="3">${escapeHtml(product?.description || '')}</textarea></label>
            ${field('Jumlah Stok', 'stock', product?.stock ?? 0, 'number', 'min="0" step="1" required')}
            <label class="block"><span class="mb-2 block font-semibold">Gambar Produk (JPG/PNG, maks. 3,5 MB)</span><input class="block w-full rounded-xl border bg-white p-3" name="image" type="file" accept="image/jpeg,image/png"></label>
            ${product?.image_path ? `<img class="max-h-48 w-fit rounded-xl" src="/api/uploads/${encodeURIComponent(product.image_path)}" alt="${escapeHtml(product.name)}">` : ''}
            <div class="flex gap-3">${button(editId ? 'Simpan Perubahan' : 'Simpan Produk')}<a class="rounded-xl border px-5 py-3 font-bold" href="/stok">Batal</a></div>
        </form>`)}`);
}

async function renderStockImport(user) {
    return layout(user, 'Impor Data', `
        <h1 class="text-3xl font-extrabold">Impor Stok</h1><p class="mt-2 text-brand-muted">Unggah Excel berisi kolom Nama Produk, Deskripsi Produk, Stok Produk, dan Jenis Produk.</p>
        ${panel(`<form class="mt-5 grid max-w-3xl gap-5" data-form="stock-import">
            <label class="block"><span class="mb-2 block font-semibold">File Excel (.xlsx, maks. 4 MB)</span><input class="block w-full rounded-xl border bg-white p-3" name="file" type="file" accept=".xlsx" required></label>
            <div class="flex flex-wrap gap-3">${button('Impor Data')}<button class="rounded-xl border px-5 py-3 font-bold" type="button" data-template="stock">Unduh Template Excel</button><a class="rounded-xl border px-5 py-3 font-bold" href="/stok">Kembali</a></div>
        </form>`)}
        <h2 class="mt-8 text-2xl font-bold">Input Manual</h2>
        ${panel(`<form class="mt-5 grid max-w-3xl gap-5" data-form="product">
            ${field('Nama Produk', 'name')}
            <label class="block"><span class="mb-2 block font-semibold">Jenis Produk</span><select class="min-h-12 w-full rounded-xl border bg-white px-4" name="category_name"><option value="">Pilih jenis</option>${['Alat Tulis','Bahan Baku Makanan','Buku','Minuman','Makanan','Perlengkapan Rumah Tangga','Barang lainnya'].map((name) => `<option>${name}</option>`).join('')}</select></label>
            <label class="block"><span class="mb-2 block font-semibold">Deskripsi Produk</span><textarea class="w-full rounded-xl border bg-white px-4 py-3" name="description" rows="3"></textarea></label>
            ${field('Stok Produk', 'stock', 0, 'number', 'min="0" step="1" required')}
            <label class="block"><span class="mb-2 block font-semibold">Gambar Produk (opsional, maks. 3,5 MB)</span><input class="block w-full rounded-xl border bg-white p-3" name="image" type="file" accept="image/jpeg,image/png"></label>
            ${button('Simpan Produk')}
        </form>`)}`);
}

async function renderTransactions(user, importPage = false) {
    if (importPage) {
        const { products } = await api('/api/products');
        return layout(user, 'Input Transaksi', `
            <div class="flex flex-wrap items-center justify-between gap-4"><h1 class="text-3xl font-extrabold">Input Transaksi</h1><a class="underline" href="/transaksi">Kembali ke Riwayat</a></div>
            ${panel(`<h2 class="text-xl font-bold">Impor Excel</h2><p class="mt-2 text-sm text-brand-muted">Kolom wajib: Nama Produk, Stok Terjual, Tanggal Terjual (YYYY-MM-DD).</p><form class="mt-5 grid max-w-3xl gap-4" data-form="transaction-import"><label class="block"><span class="mb-2 block font-semibold">File Excel (.xlsx, maks. 4 MB)</span><input class="block w-full rounded-xl border bg-white p-3" type="file" name="file" accept=".xlsx" required></label><div class="flex flex-wrap gap-3">${button('Impor Transaksi')}<button class="rounded-xl border px-5 py-3 font-bold" type="button" data-template="transactions">Unduh Template Excel</button></div></form>`)}
            <h2 class="mt-8 text-2xl font-bold">Input Manual</h2>
            ${panel(`<form class="mt-5 grid max-w-3xl gap-5" data-form="transaction">${`<label class="block"><span class="mb-2 block font-semibold">Nama Produk</span><select class="min-h-12 w-full rounded-xl border bg-white px-4" name="product_id" required><option value="">Pilih produk</option>${products.map((p) => `<option value="${p.id}">${escapeHtml(p.name)} (stok ${number(p.stock)})</option>`).join('')}</select></label>`}${field('Stok Terjual', 'quantity_sold', '', 'number', 'min="1" step="1" required')}${field('Tanggal Terjual', 'sold_at', new Date().toISOString().slice(0, 10), 'date', 'required')}<label class="block"><span class="mb-2 block font-semibold">Bukti Invoice (PDF/JPG/PNG, maks. 3,5 MB)</span><input class="block w-full rounded-xl border bg-white p-3" name="invoice" type="file" accept="application/pdf,image/jpeg,image/png"></label>${button('Simpan Transaksi')}</form>`)}`);
    }

    const params = new URLSearchParams(location.search);
    const { transactions } = await api(`/api/transactions?${new URLSearchParams({ search: params.get('search') || '', sort: params.get('sort') || 'date_desc' })}`);
    const rows = transactions.map((item, index) => row([
        number(index + 1), escapeHtml(item.product_name), number(item.quantity_sold), date(item.sold_at),
        item.invoice_path ? `<a class="underline" href="/api/uploads/${encodeURIComponent(item.invoice_path)}" target="_blank" rel="noopener">Lihat invoice</a>` : '—',
    ])).join('');
    return layout(user, 'Riwayat Transaksi', `
        <div class="flex flex-wrap items-end justify-between gap-4"><div><h1 class="text-3xl font-extrabold">Riwayat Transaksi</h1><p class="mt-2 text-brand-muted">${transactions.length} transaksi</p></div><a class="rounded-xl bg-brand-primary px-5 py-3 font-bold text-white" href="/transaksi/impor">Impor / Input Transaksi</a></div>
        ${panel(`<form class="grid gap-4 sm:grid-cols-3" method="GET">${field('Cari produk','search',params.get('search') || '')}<label class="block"><span class="mb-2 block font-semibold">Urutkan</span><select class="min-h-12 w-full rounded-xl border bg-white px-4" name="sort">${[['date_desc','Tanggal terbaru'],['date_asc','Tanggal terlama'],['quantity_desc','Stok terbanyak'],['quantity_asc','Stok tersedikit']].map(([v,l]) => `<option value="${v}" ${(params.get('sort') || 'date_desc') === v ? 'selected' : ''}>${l}</option>`).join('')}</select></label><div class="flex items-end">${button('Cari')}</div></form>`, 'my-6')}
        ${table(['No.','Nama Produk','Stok Terjual','Tanggal','Invoice'], rows)}`);
}

async function renderAnalysis(user) {
    const { products } = await api('/api/analysis');
    const rows = products.map((product, index) => row([
        number(index + 1), escapeHtml(product.name), number(product.stock),
        `${number(product.recommended_stock)}${Number(product.units_sold_in_period) === 0 ? '<div class="text-xs text-brand-muted">Data belum cukup</div>' : ''}`,
        date(new Date()),
    ])).join('');
    return layout(user, 'Analisis Stok', `
        <h1 class="text-3xl font-extrabold">Analisis Stok</h1><p class="mt-2 text-brand-muted">Rekomendasi berdasarkan penjualan 30 hari terakhir.</p>
        ${products.length ? `${table(['No.','Nama Produk','Sisa Stok','Rekomendasi Isi Stok','Tanggal'], rows)}<div class="mt-5 flex flex-wrap gap-3"><a class="rounded-xl border border-brand-primary px-5 py-3 font-bold" href="/stok">Edit Stok</a>${button('Simpan Riwayat', 'button', '') .replace('<button', '<button data-save-history')}</div>` : panel('<h2 class="text-2xl font-bold">Data stok belum ada</h2><p class="mt-2">Tambahkan produk untuk memulai analisis.</p><a class="mt-4 inline-block underline" href="/stok">Buka Data Stok</a>')}`);
}

async function renderHistory(user, historyId = null) {
    if (historyId) {
        const { history } = await api(`/api/analysis/history/${historyId}`);
        const rows = history.items.map((item, index) => row([number(index + 1), escapeHtml(item.product_name), number(item.stock), number(item.recommended_stock)])).join('');
        return layout(user, 'Riwayat Analisis', `<a class="mb-5 inline-block underline" href="/pengaturan/riwayat-analisis">Kembali</a><h1 class="mb-5 text-3xl font-extrabold">Detail Riwayat Analisis</h1><p class="mb-5 text-brand-muted">${date(history.analyzed_at)}</p>${table(['No.','Nama Produk','Stok saat analisis','Rekomendasi'], rows)}`);
    }
    const { histories } = await api('/api/analysis/history');
    const rows = histories.map((history) => row([date(history.analyzed_at), number(history.item_count), `<a class="font-semibold underline" href="/pengaturan/riwayat-analisis/${history.id}">Lihat detail</a>`])).join('');
    return layout(user, 'Riwayat Analisis', `<h1 class="mb-6 text-3xl font-extrabold">Riwayat Analisis</h1>${table(['Tanggal Analisis','Jumlah Produk','Aksi'], rows)}`);
}

async function renderSettings(user, profile = false) {
    if (!profile) {
        return layout(user, 'Pengaturan', `<h1 class="mb-7 text-3xl font-extrabold">Profil dan Akun</h1><div class="grid max-w-xl gap-3"><a class="rounded-xl bg-brand-primary px-5 py-4 font-bold text-white" href="/pengaturan/profil">Edit Profil</a><a class="rounded-xl bg-brand-primary px-5 py-4 font-bold text-white" href="/pengaturan/riwayat-analisis">Riwayat Analisis</a></div>`);
    }
    const { user: profileData } = await api('/api/profile');
    return layout(user, 'Edit Profil', `<h1 class="mb-7 text-3xl font-extrabold">Edit Profil</h1>${panel(`<form class="grid max-w-2xl gap-5" data-form="profile">${field('Nama','name',profileData.name,'text','required')}${field('Email','email',profileData.email || '','email')}${field('Password baru (opsional)','password','','password','minlength="8" autocomplete="new-password"')}${field('Konfirmasi password','password_confirmation','','password','minlength="8" autocomplete="new-password"')}<div class="flex gap-3">${button('Simpan')}<a class="rounded-xl border px-5 py-3 font-bold" href="/pengaturan">Kembali</a></div></form>`)}`);
}

async function renderPage(user) {
    if (path === '/dashboard') return renderDashboard(user);
    if (path === '/stok') return renderProducts(user);
    if (path === '/stok/tambah') return renderProductForm(user);
    if (path === '/stok/impor') return renderStockImport(user);
    if (path.startsWith('/stok/edit/')) return renderProductForm(user, path.split('/').pop());
    if (path === '/transaksi') return renderTransactions(user);
    if (path === '/transaksi/impor') return renderTransactions(user, true);
    if (path === '/analisis') return renderAnalysis(user);
    if (path === '/pengaturan') return renderSettings(user);
    if (path === '/pengaturan/profil') return renderSettings(user, true);
    if (path === '/pengaturan/riwayat-analisis') return renderHistory(user);
    if (path.startsWith('/pengaturan/riwayat-analisis/')) return renderHistory(user, path.split('/').pop());
    return layout(user, 'Tidak ditemukan', '<h1 class="text-3xl font-bold">Halaman tidak ditemukan</h1><a class="mt-4 inline-block underline" href="/dashboard">Kembali ke Dashboard</a>');
}

function showNotice(message, isError = false) {
    let notice = document.querySelector('#notice');
    if (!notice) {
        notice = document.createElement('div');
        notice.id = 'notice';
        notice.setAttribute('role', 'status');
        notice.className = 'mb-5 rounded-xl border px-4 py-3';
        document.querySelector('main')?.prepend(notice);
    }
    notice.className = `mb-5 rounded-xl border px-4 py-3 ${isError ? 'border-red-300 bg-red-50 text-red-900' : 'border-emerald-300 bg-emerald-50 text-emerald-900'}`;
    notice.textContent = message;
    notice.focus?.();
}

async function workbookTemplate(kind) {
    const XLSX = await import('@e965/xlsx');
    const rows = kind === 'stock'
        ? [['Nama Produk', 'Deskripsi Produk', 'Stok Produk', 'Jenis Produk']]
        : [['Nama Produk', 'Stok Terjual', 'Tanggal Terjual']];
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, XLSX.utils.aoa_to_sheet(rows), 'Data');
    XLSX.writeFile(workbook, kind === 'stock' ? 'template-impor-stok-visionstock.xlsx' : 'template-impor-transaksi-visionstock.xlsx');
}

function normalizeHeading(value) {
    return String(value).trim().toLowerCase().replace(/[\s_-]+/g, ' ');
}

async function parseWorkbook(file, kind) {
    const XLSX = await import('@e965/xlsx');
    if (!file || file.size > 4 * 1024 * 1024 || !file.name.toLowerCase().endsWith('.xlsx')) {
        throw new Error('Pilih file .xlsx dengan ukuran maksimal 4 MB.');
    }
    const workbook = XLSX.read(await file.arrayBuffer(), { type: 'array', cellDates: true });
    const sheet = workbook.Sheets[workbook.SheetNames[0]];
    if (!sheet) throw new Error('File Excel tidak memiliki lembar kerja.');
    const source = XLSX.utils.sheet_to_json(sheet, { defval: '', raw: true });
    if (source.length > 500) throw new Error('Maksimal 500 baris dapat diimpor sekaligus.');
    const headingMap = kind === 'stock'
        ? { 'nama produk': 'nama_produk', 'deskripsi produk': 'deskripsi_produk', 'stok produk': 'stok', 'jenis produk': 'jenis_produk' }
        : { 'nama produk': 'nama_produk', 'stok terjual': 'stok_terjual', 'tanggal terjual': 'tanggal_terjual' };
    const keys = Object.keys(source[0] || {}).reduce((map, key) => {
        map[headingMap[normalizeHeading(key)] || normalizeHeading(key)] = key;
        return map;
    }, {});
    const missing = Object.keys(headingMap).filter((heading) => !keys[headingMap[heading]]);
    if (missing.length) throw new Error(`Kolom wajib belum lengkap: ${missing.map((item) => Object.keys(headingMap).find((key) => headingMap[key] === item)).join(', ')}.`);
    return source.map((item) => Object.fromEntries(Object.entries(headingMap).map(([label, key]) => {
        const cell = item[keys[key]];
        if (key === 'tanggal_terjual' && cell instanceof Date) return [key, cell.toISOString().slice(0, 10)];
        if (key === 'tanggal_terjual' && typeof cell === 'number') {
            const parsed = XLSX.SSF.parse_date_code(cell);
            if (parsed) return [key, `${parsed.y}-${String(parsed.m).padStart(2, '0')}-${String(parsed.d).padStart(2, '0')}`];
        }
        return [key, cell];
    })));
}

async function uploadFile(file, kind) {
    if (!file) return null;
    const form = new FormData();
    form.set('file', file);
    form.set('kind', kind);
    return (await api('/api/uploads', { method: 'POST', body: form })).key;
}

async function handleSubmit(form) {
    const data = new FormData(form);
    const values = Object.fromEntries(data.entries());
    const formType = form.dataset.form;
    if (formType === 'login' || formType === 'register') {
        await api(`/api/auth/${formType}`, { method: 'POST', body: JSON.stringify(values) });
        location.href = '/dashboard';
    } else if (formType === 'product') {
        const imagePath = await uploadFile(data.get('image'), 'image');
        const payload = {
            name: values.name,
            category_name: values.category_name,
            description: values.description,
            stock: Number(values.stock),
            image_path: imagePath || undefined,
        };
        const id = form.dataset.id;
        await api(id ? `/api/products/${id}` : '/api/products', {
            method: id ? 'PATCH' : 'POST',
            body: JSON.stringify(payload),
        });
        location.href = '/stok';
    } else if (formType === 'transaction') {
        const invoicePath = await uploadFile(data.get('invoice'), 'invoice');
        await api('/api/transactions', {
            method: 'POST',
            body: JSON.stringify({
                product_id: values.product_id,
                quantity_sold: Number(values.quantity_sold),
                sold_at: values.sold_at,
                invoice_path: invoicePath,
            }),
        });
        location.href = '/transaksi';
    } else if (formType === 'stock-import' || formType === 'transaction-import') {
        const kind = formType === 'stock-import' ? 'stock' : 'transactions';
        const endpoint = kind === 'stock' ? 'products' : 'transactions';
        const rows = await parseWorkbook(data.get('file'), kind);
        const result = await api(`/api/import/${endpoint}`, { method: 'POST', body: JSON.stringify({ rows }) });
        const details = result.failures.map((failure) => `Baris ${failure.row}: ${failure.messages.join(' ')}`).join('\n');
        showNotice(`Impor selesai. ${result.imported_count} baris berhasil.${details ? `\n${details}` : ''}`, result.failures.length > 0);
        form.reset();
    } else if (formType === 'profile') {
        if (values.password && values.password !== values.password_confirmation) {
            throw new Error('Konfirmasi password belum sesuai.');
        }
        await api('/api/profile', { method: 'PATCH', body: JSON.stringify(values) });
        showNotice('Profil berhasil diperbarui.');
        form.reset();
    }
}

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-form]');
    if (!form) return;
    event.preventDefault();
    const submit = form.querySelector('button[type="submit"]');
    if (submit) submit.disabled = true;
    try {
        await handleSubmit(form);
    } catch (error) {
        showNotice(error.message, true);
    } finally {
        if (submit) submit.disabled = false;
    }
});

document.addEventListener('click', async (event) => {
    const templateButton = event.target.closest('[data-template]');
    if (templateButton) {
        workbookTemplate(templateButton.dataset.template).catch((error) => showNotice(error.message, true));
    }

    if (event.target.closest('[data-logout]')) {
        try {
            await api('/api/auth/logout', { method: 'POST', body: '{}' });
            location.href = '/';
        } catch (error) {
            showNotice(error.message, true);
        }
    }

    if (event.target.closest('[data-save-history]')) {
        try {
            await api('/api/analysis/history', { method: 'POST', body: '{}' });
            showNotice('Riwayat analisis berhasil disimpan.');
        } catch (error) {
            showNotice(error.message, true);
        }
    }
});

async function boot() {
    if (path === '/') {
        app.innerHTML = guestPage();
        return;
    }

    if (path === '/login' || path === '/register') {
        try {
            await api('/api/auth/me');
            location.href = '/dashboard';
            return;
        } catch (error) {
            if (error.status !== 401) {
                app.innerHTML = `<main class="grid min-h-screen place-items-center p-6"><section class="max-w-xl rounded-2xl bg-white p-8 shadow"><h1 class="text-2xl font-bold">VisionStock belum siap</h1><p class="mt-3 text-brand-muted">${escapeHtml(error.message)}</p></section></main>`;
                return;
            }
        }
        app.innerHTML = guestPage();
        return;
    }

    try {
        const { user } = await api('/api/auth/me');
        app.innerHTML = await renderPage(user);
    } catch (error) {
        if (error.status === 401) {
            location.href = `/login?next=${encodeURIComponent(path)}`;
            return;
        }
        app.innerHTML = `<main class="grid min-h-screen place-items-center p-6"><section class="max-w-xl rounded-2xl bg-white p-8 shadow"><h1 class="text-2xl font-bold">VisionStock belum siap</h1><p class="mt-3 text-brand-muted">${escapeHtml(error.message)}</p><a class="mt-5 inline-block underline" href="/">Kembali</a></section></main>`;
    }
}

boot();
