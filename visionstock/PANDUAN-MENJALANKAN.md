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

## Deploy ke Cloudflare

VisionStock berjalan di **Cloudflare Workers + Containers**. Container menjalankan Laravel dengan Apache/PHP, sedangkan Worker meneruskan request ke container. Fitur Containers memerlukan **Workers Paid plan**. SQLite dan file lokal tidak persisten; siapkan database PostgreSQL/MySQL eksternal dan Cloudflare R2 sebelum deployment.

1. Hubungkan repository ke **Workers Builds** dan atur **Root directory** ke folder `visionstock`.
2. Pastikan build menggunakan Node.js 22 atau lebih baru, deploy command `npm run deploy` (atau `npx wrangler deploy`), dan build command kosong. Wrangler membangun frontend serta container image dari `Dockerfile`.
3. Tambahkan Worker secrets berikut dengan perintah di folder `visionstock`:

   ```powershell
   npx wrangler secret put APP_KEY
   npx wrangler secret put POSTGRES_URL
   npx wrangler secret put AWS_ACCESS_KEY_ID
   npx wrangler secret put AWS_SECRET_ACCESS_KEY
   ```

   Masukkan `APP_KEY` yang dihasilkan oleh `php artisan key:generate --show`. Database MySQL dapat memakai secret `MYSQL_URL` alih-alih `POSTGRES_URL`. Jangan mengganti `APP_KEY` setelah aplikasi mulai digunakan.
4. Atur Worker variables melalui **Settings > Variables and Secrets**: `APP_URL`, `AWS_BUCKET`, `AWS_ENDPOINT`, dan `AWS_URL`. Untuk R2, `AWS_ENDPOINT` berformat `https://<ACCOUNT_ID>.r2.cloudflarestorage.com`, region adalah `auto`, dan `AWS_URL` harus berupa public bucket URL atau custom domain R2. Atur juga database SSL sesuai kebutuhan penyedia.
5. Jalankan migrasi **sekali** terhadap database production dari lingkungan yang memiliki akses ke database:

   ```powershell
   $env:APP_ENV = "production"
   $env:APP_KEY = "<nilai APP_KEY>"
   $env:DB_CONNECTION = "pgsql"
   $env:DB_URL = "<nilai POSTGRES_URL>"
   composer install
   php artisan migrate --force
   ```

   Untuk MySQL, ubah `DB_CONNECTION` menjadi `mysql` dan gunakan URL MySQL pada `DB_URL`.
6. Deploy menggunakan Workers Builds atau jalankan `npm run deploy` dari folder project dengan Docker engine aktif dan Wrangler sudah login.

Konfigurasi Wrangler berada di `wrangler.jsonc`; binding `VISIONSTOCK` mengarahkan request ke tiga instance stateless berbasis database dan R2. Jangan simpan secret di `wrangler.jsonc`, `.dev.vars`, atau repository. Setelah deploy, cek `https://<worker>.<subdomain>.workers.dev/up`. Jika container gagal start, periksa Worker logs dan Containers logs di Cloudflare Dashboard. Untuk menguji lokal, jalankan `npm run dev:cloudflare` dengan Docker engine aktif.
