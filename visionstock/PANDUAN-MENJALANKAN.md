# Panduan Menjalankan VisionStock

## Persiapan pertama kali

Pastikan PHP 8.3+, Composer, dan Node.js/npm sudah terpasang. Buka PowerShell di folder `visionstock`, lalu jalankan:

```powershell
composer install
npm install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
if (-not (Test-Path database\database.sqlite)) { New-Item database\database.sqlite -ItemType File }
php artisan key:generate
php artisan migrate
npm run build
```

## Menjalankan aplikasi

Buka dua terminal PowerShell di folder `visionstock`.

Terminal pertama:

```powershell
php artisan serve
```

Terminal kedua:

```powershell
npm run dev
```

Buka [http://127.0.0.1:8000](http://127.0.0.1:8000) di browser. Biarkan kedua terminal tetap berjalan selama menggunakan aplikasi. Tekan `Ctrl+C` di masing-masing terminal untuk menghentikannya.

## Menjalankan pengujian

```powershell
php artisan test
```

Database lokal menggunakan SQLite (`database\database.sqlite`). Untuk lingkungan lokal, `.env` memakai `APP_DEBUG=true`; jangan gunakan konfigurasi tersebut untuk deployment produksi.

## Riwayat Analisis

Pada halaman **Analisis Stok**, pilih **Simpan Riwayat** dan konfirmasikan untuk menyimpan snapshot stok serta rekomendasi. Daftar dan detail snapshot tersedia di **Pengaturan > Riwayat Analisis**. Riwayat tidak berubah saat data produk diperbarui.

## Deploy ke Netlify

Aplikasi Netlify menggunakan frontend statis Vite dan Netlify Functions, bukan Laravel/PHP. Konfigurasi root `netlify.toml` sudah menetapkan base directory `visionstock`, build command, direktori publish `dist`, Node.js 22, dan routing SPA/API. Saat menghubungkan repository, pilih repository ini dan biarkan pengaturan build dibaca dari `netlify.toml`; jangan menambahkan build command atau publish directory lain di dashboard.

1. Buat site baru dari repository GitHub ini. Pastikan base directory tetap `visionstock` (sudah dikonfigurasi melalui `netlify.toml`) dan build menggunakan `npm ci && npm run build`.
2. Aktifkan **Netlify Database** pada site. Netlify menerapkan file migrasi di `netlify/database/migrations/` secara otomatis saat deploy. Database ini memakai credits pada paket credit-based; cek billing dan batas penggunaan Netlify sebelum mengaktifkannya.
3. Tambahkan environment variable `APP_SESSION_SECRET` di pengaturan site, minimal 32 karakter acak. Contoh membuat nilai di PowerShell:

   ```powershell
   node -e "console.log(require('crypto').randomBytes(48).toString('base64url'))"
   ```

   Simpan nilainya sebagai secret di dashboard, jangan masukkan ke repository. Netlify menyediakan `NETLIFY_DB_URL` ketika Netlify Database aktif. Untuk upload gambar dan invoice, Netlify Blobs akan digunakan oleh Functions; periksa kuota/credits sesuai paket Netlify.
4. Deploy site. Buka URL utama, coba daftar/masuk, lalu verifikasi dashboard, stok, impor, transaksi, analisis, dan profil. Jika gagal, periksa log Functions dan pastikan Database aktif serta `APP_SESSION_SECRET` terpasang.

Impor Excel menerima `.xlsx` maksimal 4 MB dan 500 baris. Gambar/invoice dibatasi 3,5 MB karena batas payload Functions Netlify. Penggunaan Database, Functions, dan Blobs dapat mengonsumsi credits atau tunduk pada batas paket.
