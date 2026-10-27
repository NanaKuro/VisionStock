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

## Deploy gratis ke Render

VisionStock dapat dijalankan sebagai Docker Web Service pada **Render Free**. Paket gratis tidak memerlukan Workers Paid, tetapi layanan akan tidur setelah 15 menit tanpa trafik dan perlu sekitar satu menit untuk aktif kembali. Filesystem container bersifat sementara; jangan gunakan SQLite atau penyimpanan file lokal untuk data yang harus bertahan. Render Postgres Free juga kedaluwarsa setelah 30 hari, jadi gunakan penyedia PostgreSQL eksternal dengan free tier yang sesuai dan Cloudflare R2 (atau object storage S3-compatible lain) untuk unggahan. Periksa kuota dan ketentuan penyedia; jangan menambahkan metode pembayaran bila tidak ingin ada biaya.

1. Pastikan `render.yaml` di root repository terdeteksi Render Blueprint. Blueprint menunjuk ke `visionstock` sebagai root aplikasi dan memilih paket `free`.
2. Siapkan PostgreSQL dan bucket object storage. Buat URL koneksi PostgreSQL yang menerima koneksi dari luar. Untuk R2, siapkan access key, secret key, nama bucket, endpoint `https://<ACCOUNT_ID>.r2.cloudflarestorage.com`, dan public bucket URL/custom domain untuk `AWS_URL`.
3. Saat membuat Blueprint, isi environment variables yang diminta Render:
   - `APP_KEY`: hasil `php artisan key:generate --show` (buat sekali dan jangan diganti).
   - `APP_URL`: URL service Render setelah service dibuat, misalnya `https://visionstock.onrender.com`.
   - `DB_URL`: URL koneksi PostgreSQL eksternal.
   - `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_ENDPOINT`, dan `AWS_URL`: nilai object storage yang sudah disiapkan.
4. Setelah service berhasil dibuat, atur `APP_URL` ke URL publik sebenarnya pada **Environment** settings, lalu jalankan migrasi satu kali dari lingkungan lokal yang dapat mengakses database:

   ```powershell
   $env:APP_ENV = "production"
   $env:APP_KEY = "<nilai APP_KEY>"
   $env:DB_CONNECTION = "pgsql"
   $env:DB_URL = "<nilai DB_URL>"
   composer install
   php artisan migrate --force
   ```

5. Render membangun image dari `visionstock/Dockerfile`. Apache mendengarkan pada port `10000` dan health check menggunakan `/up`. Setelah deploy, buka URL `.onrender.com`; kunjungan pertama setelah layanan tidur mungkin memerlukan waktu hingga sekitar satu menit.

Konfigurasi Blueprint berada di `render.yaml`. Untuk menghindari biaya tak disengaja, pantau kuota dan batas pemakaian di Render serta penyedia database/object storage. Paket gratis cocok untuk demo/pengujian, bukan aplikasi produksi dengan jaminan uptime atau persistensi data.
