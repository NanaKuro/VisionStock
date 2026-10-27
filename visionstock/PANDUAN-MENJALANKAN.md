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

## Deploy ke Vercel

Project ini memakai Vercel Container Runtime melalui `Dockerfile.vercel`. Database SQLite dan penyimpanan file lokal tidak cocok untuk deployment karena filesystem instance Vercel tidak persisten. Siapkan database PostgreSQL atau MySQL eksternal dan bucket S3-compatible sebelum deploy.

1. Import repository ke Vercel dan pastikan **Root Directory** menunjuk ke folder `visionstock` (folder yang berisi `vercel.json` dan `Dockerfile.vercel`). `vercel.json` menetapkan Framework Preset ke `container`, agar Vercel tidak menjalankan build statis Vite atau mencari folder `dist`.
2. Buat database PostgreSQL/MySQL dan bucket S3-compatible. Bucket harus dapat menyajikan file gambar produk dan invoice melalui URL publik, sesuai perilaku aplikasi saat ini.
3. Isi Environment Variables berikut di **Project Settings > Environment Variables** untuk Production, Preview, dan Development sesuai kebutuhan:
   - `APP_NAME=VisionStock`, `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL` dengan URL deployment.
   - `APP_KEY` dari `php artisan key:generate --show`; jangan mengganti key setelah pengguna mulai memakai aplikasi.
   - PostgreSQL: `DB_CONNECTION=pgsql` dan `POSTGRES_URL` dari penyedia database (atau isi `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`). Untuk MySQL, gunakan `DB_CONNECTION=mysql` dan `MYSQL_URL` atau variabel koneksi terpisah. Untuk PostgreSQL, gunakan `DB_SSLMODE=require` bila diwajibkan penyedia database.
   - `SESSION_DRIVER=cookie`, `SESSION_SECURE_COOKIE=true`, `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`, dan `LOG_CHANNEL=stderr`.
   - `FILESYSTEM_DISK=s3`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, dan jika diperlukan `AWS_URL`, `AWS_ENDPOINT`, serta `AWS_USE_PATH_STYLE_ENDPOINT`.
4. Jalankan migrasi satu kali pada database produksi sebelum aplikasi dipakai. Contoh dari PowerShell setelah memasang Vercel CLI dan mengisi environment Production:

   ```powershell
   vercel env pull .env.production --environment production
   composer install
   php artisan migrate --env=production --force
   ```

5. Deploy dari Vercel atau jalankan `vercel --prod`.

Contoh variabel tersedia di `.env.vercel.example`. Di environment production, aplikasi kini memilih PostgreSQL, session cookie aman, cache array, dan queue sinkron secara default agar tidak bergantung pada file SQLite lokal atau tabel session/cache/jobs. Kredensial database tetap wajib diatur; `POSTGRES_URL` dan `MYSQL_URL` didukung untuk mengisi URL koneksi langsung. Jangan commit nilai rahasia atau file `.env.production`. Vercel membangun aset frontend dan dependensi PHP dari Dockerfile; tidak perlu mengisi Build Command atau Output Directory manual. Untuk menguji image secara lokal, jalankan `docker build -f Dockerfile.vercel -t visionstock .` dari folder project dengan Docker engine aktif.
