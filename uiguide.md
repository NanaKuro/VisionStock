# UIGUIDE.md — Panduan Detail UI VisionStock

> Pelengkap `prompt.md`. Dokumen ini merangkum hasil CSS dari Figma (kanvas acuan **1440 × 1024 px**) menjadi spesifikasi yang bisa langsung dipakai di Laravel Blade + Tailwind CSS.
> Nilai yang berulang di banyak layar hanya ditulis sekali di bagian Design Tokens dan Komponen.
> **Tampilan harus fleksibel mengikuti ukuran layar pengguna** (lihat §7). Angka 1440 px di dokumen ini adalah ukuran acuan, bukan ukuran tetap.

---

## 0. Hubungan Dokumen Ini dengan File SVG

File SVG (`docs/ui/*.svg`) adalah **sumber visual utama**. `uiguide.md` **menjelaskan dan memperinci** SVG tersebut: nama komponen, ukuran, warna, tipografi, teks persis, urutan elemen, dan perilaku yang tidak terlihat dari gambar statis (state, interaksi, perilaku responsif).

| Hal yang dicari | Acuan |
|---|---|
| Bentuk, posisi, dan isi visual tiap layar | File SVG terkait (nama file tercantum di setiap bagian §4) |
| Angka detail (ukuran, warna, font, radius, jarak) dan teks label | `uiguide.md` §1–§4, yang berasal dari CSS Figma |
| Perilaku yang tidak ada di SVG (responsif, hover/fokus, state aktif sidebar) | `uiguide.md` §5–§7 |
| Urutan alur antar layar | `docs/user-flow.png` |

Cara memakai:
1. Sebelum mengerjakan sebuah layar, **buka SVG yang tercantum** di bagian §4 layar tersebut, lalu baca rincian di `uiguide.md`.
2. Jika SVG dan `uiguide.md` berbeda untuk hal **visual**, ikuti SVG/Figma dan catat selisihnya di §5. Untuk hal **perilaku** yang tidak digambar di SVG, ikuti `uiguide.md`.
3. Jangan menambah atau menghilangkan elemen yang ada di SVG tanpa izin.

---

## 1. Design Tokens

### Warna
| Token | Hex | Pemakaian |
|---|---|---|
| `bg-primary` | `#A3DED3` | Latar halaman, tombol pill di top bar, item menu sidebar, latar modal |
| `secondary` | `#51B0A5` | Top bar, sidebar, kartu, tombol utama, header tabel |
| `text` | `#5F242F` | Teks utama, label, ikon outline |
| `text-2` | `#FFFFFF` | Teks di atas `secondary` |
| `lineart` | `#333333` | Border input form |
| `status` | `#828282` | Placeholder & teks status |
| `error` | `#EB5757` | Border dan pesan error, ikon X |
| `overlay` | `rgba(0,0,0,0.45)` | Latar gelap di belakang modal |
| `zebra` | `rgba(224,224,224,0.4)` | Baris genap tabel |

### Tipografi (font **Poppins**, semua heading bold 700)
| Token | Ukuran / Line-height | Pemakaian |
|---|---|---|
| `h1` | 56 / 62 | Headline landing page |
| `h2` | 48 / 53 | Judul form auth, sapaan dashboard, angka statistik, judul modal |
| `h4` | 32 / 20 | Brand, judul sidebar, judul seksi, label statistik, nama "Admin" |
| `h5` | 24 / 14 | Label input, tombol besar, item menu sidebar |
| `text-lg-bold` | 20 / 28 (700) | Tombol sedang, label filter, nama produk |
| `text-lg` | 20 / 28 (400) | Teks bantu dropzone |
| `text-md-bold` | 18 / 25 (700) | Tombol & header kolom tabel, "Cari" |
| `text` | 16 / 22 (400) | Placeholder, isi tabel, label radio |
| `text-sm` | 14 / 20 (400) | Pesan status/error |

### Radius, bayangan, spasi
- Radius: **16px** (tombol, kartu, input, modal, gambar), **4px** (dropdown, header tabel), **50px/penuh** (avatar).
- Bayangan top bar: `0 4px 5px rgba(0,0,0,.25)`; sidebar: `4px 0 5px rgba(0,0,0,.25)`; gambar landing: `0 8px 8px rgba(0,0,0,.25)`.
- Skala spasi yang dipakai: 8, 10, 12, 16, 24, 32, 64.
- Ikon: gaya **outline**, warna `text` (`#5F242F`), ketebalan garis ±3,5px, ukuran 40px (24px untuk ikon pencarian). Di atas `secondary` ikon berwarna putih.

```js
// tailwind.config.js (potongan)
theme: { extend: {
  colors: { primary:'#A3DED3', secondary:'#51B0A5', ink:'#5F242F', lineart:'#333333',
            muted:'#828282', danger:'#EB5757' },
  fontFamily: { sans:['Poppins','sans-serif'] },
  borderRadius: { xl16:'16px' },
}}
```

---

## 2. Kerangka Layout (halaman setelah login)

| Bagian | Spesifikasi |
|---|---|
| Top bar | Tinggi 104–106, lebar penuh, bg `secondary`, padding 32×64, bayangan bawah. Isi kiri: ikon menu (40) + ikon home (40) jarak 24, lalu teks **VisionStock** (h4 putih). Isi kanan (hanya Data Stok & Riwayat Transaksi): pill **Cari** + tombol aksi (lihat §3). |
| Sidebar | Lebar **367**, tinggi ±922, bg `secondary`, padding 32, bayangan kanan. Atas: **judul halaman** (h4 putih, rata tengah). Tengah: 4 item menu, jarak antar item 64. Bawah: avatar bulat 60 (bg `bg-primary`, ikon user) + teks **Admin** (h4 putih), jarak 32. |
| Item menu | Lebar 303, tinggi 72 (Riwayat Transaksi 87 karena dua baris), bg `bg-primary`, radius 16, padding 16×32, ikon 40 + label h5 `text`, jarak 24. Urutan: **Data Stok, Analisis Stok, Riwayat Transaksi, Pengaturan**. |
| Area konten | Mulai x=367, lebar 1073, padding sekitar 32×64, jarak antar blok 32–64. Boleh scroll vertikal (Riwayat Transaksi ±1242 px). |

Judul sidebar mengikuti halaman: Dashboard, Data Stok, Analisis Stok, Riwayat Transaksi, Impor Data, Input Transaksi, Pengaturan.
Desain Figma hanya ada untuk lebar 1440 px, sehingga layout lebih kecil harus diturunkan dari desain ini mengikuti aturan **§7 Responsivitas** (sidebar menjadi drawer, grid menyusut, dsb.).

---

## 3. Komponen Reusable

**Tombol utama** — bg `secondary`, radius 16, teks putih bold. Dua ukuran: besar (padding 12×28, tinggi 38, teks 24) dan sedang (padding 12×28, tinggi 52, teks 20). Jarak antar tombol 24 (besar) atau 32 (sedang).
**Tombol pill top bar** — bg `bg-primary`, tinggi 42, padding 8×24, teks 18 bold `text`, radius 16. Pencarian: lebar 231, teks "Cari" + ikon cari 24. Tombol aksi: "Impor Stok" (149) atau "Impor Transaksi" (199).
**Input teks** — tinggi 55, border 1px `lineart`, radius 16, placeholder 16 `muted` dengan padding kiri 24. Label di atas (h5 `text`), jarak label–input 16, jarak antar field 24–32. Pesan status di bawah (text-sm).
  - *Error*: border `danger` + pesan `danger`, contoh "Username/email wajib diisi!".
**Dropdown** — tinggi 55, border 1px, radius **4**, chevron di kanan (25 px dari tepi). Pada filter: border & teks `bg-primary`/putih; pada form: border & teks `text`.
**Radio** — lingkaran 24, border 2px `bg-primary`, label 16 putih, jarak 12.
**Tombol unggah** — bg `secondary` (di form) atau `bg-primary` (di dropzone), ikon unggah 40 + teks "Unggah" 20 bold.
**Kartu statistik** — judul h4 `text` rata tengah, di bawahnya kotak `secondary` 264×204 radius 16 berisi angka h2 putih.
**Kartu filter** — bg `secondary`, radius 16, padding 32×64, lebar 945; judul "Filter" h4 putih; tiga kolom (lihat §4.3).
**Tabel** — lebar 880. Judul seksi h4 warna `secondary`. Header: tinggi 64, bg `secondary`, radius 4, teks 18 bold `text`. Baris: tinggi 63, teks 16 `text`, baris genap memakai `zebra`. Posisi kolom (x): No 24, Nama Produk 94, kolom 3 = 316, kolom 4 = 539, kolom 5 = 761.
**Modal** — overlay `overlay` menutup seluruh layar; kotak bg `bg-primary`, radius 16, padding 32×64, jarak 32–64, di tengah layar.
**Badge sukses** — lingkaran 164 `secondary` dengan centang putih. **Ikon gagal/kosong** — lingkaran X 264, garis `danger`.

---

## 4. Spesifikasi per Layar

### 4.1 Landing Page (`Landing Page - Novi.svg`)
- Top bar 106: kiri brand VisionStock, kanan tombol pill **Masuk** (111×42) dan **Daftar** (108×42).
- Hero (mulai y=223, padding 0×64, jarak kolom 240): kiri headline **"Solusi Permasalahan Stok"** (h1, lebar 448, `text`) + tombol **"Coba Sekarang"** (247×38); kanan foto 661×661 (rak penyimpanan), radius 16 dengan bayangan.

### 4.2 Masuk, Registrasi, dan state error
(`Masuk Login*.svg`, `Login/registrasi - Novi.svg`, `Form registrasi.svg`, `Error login.svg`, `Error registrasi.svg`)
- Dua kolom: kiri foto 680×1024 (rak toko), kanan panel 760 lebar (konten di tengah, padding 0×32, jarak 64).
- Judul (**Masuk** / **Registrasi**) h2 `text`. Form lebar 632.
- Masuk: Username/Email, Password. Registrasi: Username/Email, Password, Ulangi Password.
- Tombol **Masuk** (139×38) dan **Daftar** (135×38) berdampingan.
- Error: semua field bermasalah berborder `danger` dengan pesan `danger` di bawahnya.

### 4.3 Dashboard (`Dashboard.svg`)
- Baris 1: kartu sapaan 630×313 (`secondary`, radius 16) berisi **"Halo, Admin!"** (h2) dan "Ingin melakukan apa hari ini?" (h4), semuanya putih; di sebelah kanan kartu statistik **Total Riwayat Transaksi**.
- Baris 2: tiga kartu statistik: **Total Produk**, **Jumlah Produk**, **Jumlah Transaksi**.
- Contoh data desain: 336 / 33 / 550 / 336.

### 4.4 Data Stok (`Data Stok.svg`, `Data Stok berhasil.svg`)
- Top bar: Cari + **Impor Stok**.
- Kartu filter (945×224), tiga kolom: **Berdasarkan Abjad** (Abjad Z–A, Abjad A–Z), **Berdasarkan Stok** (Stok terbanyak, Stok tersedikit), **Berdasarkan Jenis Produk** (dropdown 320, placeholder "Jenis Produk").
- Grid produk 3 kolom: kartu 264 lebar, foto 264×204 dengan sudut atas membulat 16, label nama produk (20 bold putih) di atas pita `secondary`; tombol **Edit Stok** (145×52) tepat di bawah kartu. Contoh: Tepung, Pena, Buku, Telur, Beras, Susu.
- *Empty state* (`Data Stok 2 - Novi.svg`): ikon X `danger` 264, judul **"Data stok belum ada nih"** (h2), teks **"Ayo lakukan input stok atau impor dari pencatatan sebelumnya."** (h4, lebar 570), satu tombol utama 191×38 (label tidak terbaca dari CSS; verifikasi di Figma, kemungkinan menuju Impor Data).

### 4.5 Edit Stok (`Edit stok.svg`, `Edit stok terisi.svg`)
- Modal 1073×744 di atas Data Stok (overlay gelap). Isi dari atas: foto produk 342×264 (radius 16);
  1. **Nama Produk** (lebar penuh 945);
  2. **Deskripsi Produk** (726) + **Gambar Produk** tombol Unggah (202×55);
  3. **Stok Produk** (447) + **Jenis Produk** dropdown (483);
  4. tombol **Simpan** (152×38) dan **Batal** (122×38).
- Versi terisi menampilkan data produk yang dipilih, misalnya "Tepung Roti", "Tepung serbaguna", stok 20, "Bahan makanan".

### 4.6 Impor Data / Impor Stok (`Impor Data*.svg`, `Notif impor berhasil.svg`)
- Sidebar judul "Impor Data". Dua seksi bertumpuk (heading h4 `text`, jarak 64):
  1. **Unggah Excel** — dropzone 945×292 (`secondary`, radius 16): tombol **Unggah** 202×72 (`bg-primary`) + teks bantu "Pilihlah sebuah file atau tahan dan lepaskan disini Maksimum ukuran 10 MB" (20 putih).
     - *Mengunggah*: tombol berganti chip nama file dengan persentase, mis. "Data Penjualan.xlsx (90%)" + teks "File Sedang Diunggah…".
     - *Selesai*: chip "(100%)" + "File berhasil diunggah.", muncul tombol **Simpan** dan **Reset**.
  2. **Impor Manual** — form seperti §4.5 (Nama Produk, Deskripsi + Gambar, Stok + Jenis Produk) dengan tombol **Simpan** (152×38) dan **Reset** (125×38).
- Setelah simpan: modal sukses 739×461, judul **"Data Berhasil disimpan"** (h2), badge sukses, satu tombol 130×52.

### 4.7 Analisis Stok (`Analisis Stok.svg`, `Konfirmasi notif*.svg`)
- Judul seksi **"Stok Saat Ini"**; tabel 5 kolom: **No, Nama Produk, Sisa Stok, Rekomendasi Isi Stok, Tanggal**. Contoh baris: Tepung 20→25, Pena 30→35, Buku 25→30, Telur 45→50, Beras 10→20, Susu 30→35 (tanggal 1/1/2026).
- Di bawah tabel: **Edit Stok** (145×52) dan **Kirim Notifikasi ke WhatsApp** (357×52), jarak 32.
- Modal konfirmasi 739×286: teks **"Ingin Mengirim analisis ini ke WhatsApp?"** (h2) + tombol **Kirim Notifikasi ke WhatsApp** (357×52) dan **Batal** (111×52).
- Modal sukses: judul **"Analisis berhasil dikirim"** + badge + satu tombol 130×52.

### 4.8 Riwayat Transaksi (`Riwayat Transaksi.svg`)
- Top bar: Cari + **Impor Transaksi**.
- Kartu filter sama dengan Data Stok, tetapi kolom ketiga **Berdasarkan Tanggal** (dropdown "Pilih Tanggal").
- Tabel: **No, Nama Produk, Stok Terjual, Tanggal, Invoice** (ikon gambar 40×40 di kolom Invoice). Tombol **Edit Transaksi** (201×52) di bawah tabel.

### 4.9 Impor / Input Transaksi (`Impor transaksi*.svg`)
- Sidebar judul "Input Transaksi". Struktur sama dengan §4.6 (dropzone Excel + Impor Manual), perbedaan formulir manual:
  - **Nama Produk** (945); **Stok Terjual** (726) + **Bukti Invoice** tombol Unggah (202×55); **Tanggal Terjual** dropdown (483, "Pilih Tanggal"); tombol **Simpan** / **Reset**.
- State unggah: "Data Transaksi.xlsx (77%)" → "(100%)". Modal sukses: **"Transaksi Berhasil Disimpan"**.

### 4.10 Pengaturan (`Pengaturan*.svg`)
- Awal: judul **"Profil dan Akun"** + dua tombol lebar 511×46 (`secondary`, teks h5 putih, rata kiri): **Edit Profil**, **Hubungkan ke WhatsApp**.
- **Edit Profil**: field lebar 945 — Nama, Email, Atur Ulang Password, Konfirmasi Password; tombol **Simpan** (152×38) dan **Kembali** (160×38).
- **Hubungkan ke WhatsApp**: field **Nomor Telepon** (945); tombol **Kirim** (123×38) dan **Kembali** (160×38).

---

## 5. Catatan Ketidakkonsistenan di Desain (perlu keputusan)

1. Teks di CSS `Error login` bertuliskan **"Registrasi"**, seharusnya "Masuk". Gunakan "Masuk" untuk layar login.
2. Judul sidebar di `Konfirmasi notif-1` tertulis **"Analisis Data"**; gunakan **"Analisis Stok"**.
3. Judul tabel di Riwayat Transaksi masih **"Stok Saat Ini"** (salinan dari Analisis Stok); disarankan **"Riwayat Transaksi"**.
4. Nama file contoh di dropzone ditulis **".xlxs"**; benar **".xlsx"**.
5. Dashboard menampilkan **Total Riwayat Transaksi** dan **Jumlah Transaksi** dengan angka sama (336). Tentukan apakah keduanya benar-benar berbeda metrik.
6. Item sidebar tidak punya **state aktif** di desain; tambahkan penanda (mis. bg `secondary` lebih gelap atau garis kiri) dan tandai sebagai keputusan baru.
7. Label tombol di modal sukses dan empty state tidak terbaca dari CSS; verifikasi di Figma.

### State yang belum ada di desain (perlu dibuat mengikuti gaya yang sama)
- Empty state **Riwayat Transaksi** dan **Analisis Stok** (user flow menyebut keduanya).
- Notifikasi **berhasil/gagal terhubung ke WhatsApp** dan **WhatsApp belum terhubung** saat kirim notifikasi.
- Error validasi pada form impor (file terlalu besar, format salah, baris Excel tidak valid) dan loading tabel.
- Tampilan layar kecil/tablet (ditangani lewat aturan di §7, belum ada frame Figma-nya).

---

## 6. Aturan untuk AI saat Mengimplementasikan UI

1. Gunakan **design tokens** di §1; jangan menulis hex atau ukuran font langsung di komponen.
2. Bangun komponen Blade reusable: `topbar`, `sidebar`, `menu-item`, `button`, `pill`, `input`, `dropdown`, `radio`, `upload-box`, `stat-card`, `filter-card`, `data-table`, `modal`, `success-badge`.
3. Ukuran di Figma (1440 px) **wajib** diubah ke satuan fleksibel (lihat §7), tetapi proporsi, warna, radius, dan hierarki teks harus tetap sama.
4. Semua teks antarmuka memakai **teks persis seperti di desain** (Bahasa Indonesia), kecuali poin di §5 yang sudah diputuskan.
5. Setiap layar harus memiliki state: normal, error, loading, sukses, dan kosong.
6. Jangan menambah elemen visual baru (ilustrasi, warna, animasi) tanpa izin.

---

## 7. Responsivitas: UI Menyesuaikan Ukuran Layar Pengguna

Seluruh halaman harus nyaman dipakai di layar ponsel, tablet, laptop, hingga monitor lebar. Ukuran 1440 px di Figma menjadi **acuan proporsi** untuk layar desktop, bukan lebar tetap.

### 7.1 Prinsip umum
- Gunakan **layout fluid**: `w-full` + `max-w-*`, flexbox/grid, dan satuan relatif (`rem`, `%`, `vw`, `clamp()`). Jangan memakai lebar/tinggi piksel tetap untuk wadah (mis. `width: 945px`, `height: 1024px`); gunakan `min-h-screen` atau `min-h-dvh`.
- Pendekatan **mobile-first**: tulis gaya dasar untuk layar kecil, lalu tambahkan breakpoint Tailwind `sm 640`, `md 768`, `lg 1024`, `xl 1280`, `2xl 1536`.
- Halaman **tidak boleh scroll horizontal**; konten lebar (tabel) scroll di dalam wadahnya sendiri.
- Area sentuh (tombol, tautan, ikon) minimal **44 × 44 px** di layar sentuh; ukuran font input minimal 16 px agar tidak ter-zoom di iOS.
- Gunakan `object-cover` dan `aspect-ratio` untuk gambar; foto produk memakai rasio 264:204.
- Pastikan layar tetap terbaca saat zoom browser hingga 200 % dan pada orientasi lanskap.

### 7.2 Penyesuaian tipografi dan spasi
| Token | Ukuran di 1440 px | Ukuran fleksibel (contoh) |
|---|---|---|
| `h1` | 56 | `clamp(32px, 4vw, 56px)` |
| `h2` | 48 | `clamp(28px, 3.4vw, 48px)` |
| `h4` | 32 | `clamp(20px, 2.2vw, 32px)` |
| `h5` | 24 | `clamp(16px, 1.7vw, 24px)` |
| `text-lg` / `text-md` / `text` | 20 / 18 / 16 | 16–20 (tidak lebih kecil dari 14 untuk teks isi) |

Padding halaman: `px-4` (ponsel) → `md:px-8` → `xl:px-16`. Jarak antar blok 64 px di desktop menyusut ke 24–32 px di ponsel.

### 7.3 Kerangka halaman
| Layar | Perilaku |
|---|---|
| **≥ 1024 px (lg)** | Sidebar tampil permanen di kiri, lebar `clamp(240px, 25.5vw, 367px)` (367 ÷ 1440 ≈ 25,5 %). Konten mengisi sisanya dengan `max-w` agar tidak terlalu lebar di monitor besar (mis. 1200–1440 px, rata tengah). |
| **< 1024 px** | Sidebar menjadi **drawer** (off-canvas) dari kiri, dibuka lewat ikon menu di top bar, dengan overlay `overlay` dan bisa ditutup dengan klik di luar/tombol Esc. Konten memakai lebar penuh. |
| Top bar | Tetap menempel di atas (`sticky top-0`). Brand tetap terlihat; pada `< md` pill **Cari** berubah menjadi ikon cari yang membuka kolom input, dan tombol aksi (Impor Stok/Transaksi) boleh berpindah ke bawah judul halaman atau menjadi ikon + label pendek. |

### 7.4 Penyesuaian per layar
- **Landing Page**: `lg` ke atas dua kolom (teks kiri, foto kanan). Di bawah `lg` ditumpuk: headline, tombol **Coba Sekarang**, lalu foto dengan `w-full max-w-[661px]` dan rasio 1:1. Tombol Masuk/Daftar tetap di top bar (ukuran lebih kecil di ponsel).
- **Masuk / Registrasi**: `lg` ke atas dua kolom (foto 680 ÷ 1440 ≈ 47 % kiri, form kanan). Di bawah `lg` foto disembunyikan atau menjadi banner tipis di atas; form di tengah dengan `w-full max-w-[632px]` dan padding 16–32. Tombol Masuk/Daftar ditumpuk penuh lebar pada `< sm`.
- **Dashboard**: `lg` = baris 1 sapaan (2/3) + kartu statistik (1/3), baris 2 tiga kartu; `md` = grid 2 kolom; `< sm` = 1 kolom. Kartu statistik memakai `aspect-[264/204]` atau tinggi minimum, bukan 204 px tetap.
- **Data Stok**: kartu filter bergeser dari 3 kolom (`lg`) → 2 kolom (`md`) → 1 kolom (`< md`), dropdown `w-full`. Grid produk: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3` (boleh `2xl:grid-cols-4`). Tombol Edit Stok `w-full` pada ponsel.
- **Tabel (Analisis Stok & Riwayat Transaksi)**: bungkus dalam `overflow-x-auto` dengan `min-w` yang menjaga kolom tetap terbaca, header ikut tampil. Pada `< sm`, boleh diubah menjadi **daftar kartu** (tiap baris menjadi kartu dengan pasangan label : nilai). Kolom Invoice tetap berupa ikon yang bisa disentuh. Tombol di bawah tabel (Edit Stok, Kirim Notifikasi ke WhatsApp, Edit Transaksi) dibungkus (`flex-wrap`) dan menjadi penuh lebar pada ponsel.
- **Form impor, Edit Stok, Edit Profil, Hubungkan WhatsApp**: semua input `w-full`. Baris berpasangan (Deskripsi + Unggah, Stok + Jenis Produk, Stok Terjual + Bukti Invoice) menjadi `flex-col` di bawah `md` dan `flex-row` mulai `md`. Tombol Simpan/Reset/Batal/Kembali boleh ditumpuk penuh lebar pada `< sm`.
- **Dropzone Excel**: lebar penuh; tombol Unggah dan teks bantu ditumpuk; nama file pada chip dipotong dengan `truncate` bila panjang.
- **Modal**: `w-[min(92vw, 1073px)]` (untuk modal kecil `min(92vw, 739px)`), `max-h-[90dvh]` dengan `overflow-y-auto`, padding 16 (ponsel) → 64 (desktop). Tombol di dalam modal dibungkus dan menjadi tumpukan di ponsel. Fokus terkunci di dalam modal dan modal bisa ditutup dengan Esc.
- **Empty state & badge sukses**: ukuran ikon fleksibel `clamp(120px, 20vw, 264px)` untuk ikon X, `clamp(96px, 14vw, 164px)` untuk badge.
- **Pengaturan**: tombol menu `w-full max-w-[511px]`; formulir mengikuti aturan form di atas.

### 7.5 Pengujian responsif (wajib sebelum tahap selesai)
Uji minimal pada lebar: **360, 390, 768, 1024, 1280, 1440, 1920 px**. Setiap layar harus memenuhi:
- [ ] Tidak ada scroll horizontal pada halaman.
- [ ] Tidak ada teks terpotong atau elemen saling menimpa.
- [ ] Semua aksi (tombol, filter, menu) tetap bisa dijangkau.
- [ ] Sidebar/drawer, modal, dan tabel berfungsi di ukuran kecil.
- [ ] Tampilan di 1440 px tetap setia pada SVG/Figma.

