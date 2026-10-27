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

## Deploy ke Railway

Railway akan membangun Docker image dari `Dockerfile` di folder aplikasi. Karena aplikasi berada di subfolder repository, atur **Root Directory** service ke `/visionstock`; biarkan Railway mendeteksi Dockerfile secara otomatis. Container mendengarkan pada nilai `PORT` yang diberikan Railway (default `8080`) dan menyediakan health endpoint Laravel `/up`.

1. Buat project Railway dan tambahkan service dari repository GitHub ini. Pada **Settings > Source**, atur **Root Directory** ke `/visionstock`. Jangan mengisi build atau start command khusus; gunakan Dockerfile dan perintah `CMD` yang sudah disediakan.
2. Pada **Settings > Networking**, buat domain publik untuk service. Atur variabel berikut pada tab **Variables**:
   - `APP_ENV=production` dan `APP_DEBUG=false`.
   - `APP_KEY`: hasil `php artisan key:generate --show` (buat sekali dan jangan diganti).
   - `APP_URL`: URL publik Railway, misalnya `https://<domain-service>.up.railway.app`.
   - `DB_CONNECTION=pgsql`, `DB_SSLMODE=require`, dan `DB_URL`: URL PostgreSQL dari database Railway atau penyedia eksternal.
   - `SESSION_DRIVER=cookie`, `SESSION_SECURE_COOKIE=true`, `CACHE_STORE=array`, dan `QUEUE_CONNECTION=sync`.
   - Untuk berkas unggahan persisten, atur `FILESYSTEM_DISK=s3`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_ENDPOINT`, `AWS_URL`, `AWS_DEFAULT_REGION=auto`, dan `AWS_USE_PATH_STYLE_ENDPOINT=true` sesuai bucket R2/S3-compatible. Jangan mengandalkan filesystem container untuk menyimpan berkas.
3. Pada pengaturan **Healthcheck Path**, isi `/up`. Pastikan Railway menggunakan port dari variabel `PORT`; Dockerfile mengatur Apache mengikuti port tersebut.
4. Deploy service, lalu jalankan migrasi **satu kali** terhadap database yang dikonfigurasi dari lingkungan lokal yang memiliki Composer serta akses jaringan ke database:

   ```powershell
   $env:APP_ENV = "production"
   $env:APP_KEY = "<nilai APP_KEY>"
   $env:DB_CONNECTION = "pgsql"
   $env:DB_URL = "<nilai DB_URL>"
   composer install
   php artisan migrate --force
   ```

5. Setelah migrasi selesai, buka domain Railway dan periksa log service bila health check atau startup gagal. Paket, kuota, dan biaya Railway bergantung pada akun dan pemakaian; periksa halaman billing Railway dan layanan database/object storage yang digunakan.
