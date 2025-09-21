# Gerindra Web Management System

Gerindra Web Management System adalah platform manajemen konten berita dan profil organisasi berbasis **Laravel 12** dan **Filament v4**. Proyek ini dirancang untuk kebutuhan DPP Partai Gerindra agar dapat mengelola berita, halaman statis, struktur organisasi, serta menampilkan konten resmi di situs publik dengan antarmuka yang mudah digunakan.

![Laravel](https://img.shields.io/badge/Laravel-12-red.svg) ![Filament](https://img.shields.io/badge/Filament-v4-blue.svg) ![PHP](https://img.shields.io/badge/PHP-8.3-blueviolet.svg) ![License](https://img.shields.io/badge/license-MIT-green.svg)

---

## Daftar Isi
1. [Fitur Kunci](#fitur-kunci)
2. [Arsitektur & Teknologi](#arsitektur--teknologi)
3. [Persyaratan Sistem](#persyaratan-sistem)
4. [Instalasi Cepat](#instalasi-cepat)
5. [Seeder & Data Awal](#seeder--data-awal)
6. [Penggunaan Admin (Filament)](#penggunaan-admin-filament)
7. [Struktur Direktori Penting](#struktur-direktori-penting)
8. [Perintah Artisan Berguna](#perintah-artisan-berguna)
9. [Panduan Deploy](#panduan-deploy)
10. [Lisensi](#lisensi)

---

## Fitur Kunci
- **Dashboard Filament v4** lengkap dengan role Admin, Editor, dan Penulis.
- **Berita multi-kategori** (primary category + kategori tambahan) serta tag yang fleksibel.
- **Homepage dinamis** dapat diatur melalui Filament: hero slider, tombol khusus, struktur pengurus, tabs konten, dan blok kategori.
- **Menu bertingkat** (multi-level) dengan builder drag & drop, mendukung sub–submenu.
- **Halaman statis** dengan dukungan unggah PDF & viewer langsung di browser.
- **Seeder komprehensif** untuk berita, kategori, tag, menu, halaman profil, dan company profile.
- **Optimasi media** (varian WebP, thumbnail) otomatis melalui queue lokal.
- **Statistik populer** berdasarkan `post_views` untuk widget berita populer.
- **Mode responsif** menggunakan Blade + Tailwind + Alpine.

---

## Arsitektur & Teknologi
- **Backend**: Laravel 12, Eloquent ORM.
- **Admin Panel**: Filament v4.
- **Frontend**: Blade, TailwindCSS, Alpine.js.
- **Database**: MySQL/MariaDB.
- **Media**: Laravel Filesystem (disk `public`) + intervensi gambar.
- **Tooling**: Vite, npm, Composer.

---

## Persyaratan Sistem
- PHP 8.3 atau lebih baru dengan ekstensi: `bcmath`, `ctype`, `curl`, `fileinfo`, `gd/imagick`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`.
- Composer 2 & Node.js 18 (atau lebih baru) + npm.
- MySQL/MariaDB.
- Web server (Nginx/Apache) + akses ke `storage`/`bootstrap/cache`.

Opsional: `jpegoptim`, `optipng`, `pngquant`, `gifsicle`, `cwebp` untuk optimasi gambar tingkat server.

---

## Instalasi Cepat
```bash
git clone https://github.com/akmaller/Gerindra-Web_Management_System.git
cd Gerindra-Web_Management_System
cp .env.example .env
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan key:generate
php artisan storage:link
```

Edit `.env` dan sesuaikan konfigurasi (database, URL, mail, dsb). Kemudian jalankan migrasi & seeder:

```bash
php artisan migrate --force
php artisan db:seed
```

Buat akun Filament jika diperlukan:
```bash
php artisan make:filament-user
```

Jalankan aplikasi:
```bash
php artisan serve
```

---

## Seeder & Data Awal
Menjalankan `php artisan db:seed` akan memuat data berikut:

| Seeder | Deskripsi |
| --- | --- |
| `SiteSettingsSeeder` | Nama situs, deskripsi, logo awal. |
| `CompanyProfileSeeder` | Company profile default (nama, alamat, email). |
| `PageSeeder` | 10 halaman profil (Profil Partai, Deklarasi, Sejarah, Visi-Misi, dst) + halaman umum. |
| `NewsPostSeeder` | 13 berita resmi beserta kategori, tag, dan tree menu `Gerindra → Profil`. |
| `CategorySeeder`, `RolesSeeder` | Referensi kategori dasar & role pengguna. |

Anda bisa menjalankan seeder tertentu secara mandiri, misalnya:
```bash
php artisan db:seed --class=NewsPostSeeder
```

---

## Penggunaan Admin (Filament)
Akses panel di `https://domain-anda.com/admin` lalu login dengan akun yang dibuat.

### Modul Penting
- **Posts**: kelola berita, kategori (bisa pilih lebih dari 1), tag, status, sorotan.
- **Pages**: kelola halaman statis, unggah PDF lampiran, atur thumbnail & konten.
- **Menus**: drag & drop menu bertingkat; sub-menu dapat memiliki sub-menu lagi.
- **Homepage Settings**: atur hero slider, tombol khusus, susunan pengurus, tabs konten, pilihan kategori, dan teks lainnya.
- **Company Profile**: ubah nama perusahaan, alamat, email, sosial media.

---

## Struktur Direktori Penting
```
app/
 ├── Filament/           # Halaman Filament & resource admin
 ├── Http/Controllers/   # Controller frontend
 ├── Models/             # Eloquent model (Post, Category, Menu, dll)
 └── Services/           # Service pendukung (PopularPosts)

resources/views/
 ├── home.blade.php      # Halaman utama
 ├── pages/show.blade.php# Halaman statis + PDF embed
 ├── partials/           # Header, footer, navigation
 └── post/               # Tampilan detail berita, kartu, dsb

database/
 ├── migrations/         # Skema tabel
 └── seeders/            # Seeder data awal
```

---

## Perintah Artisan Berguna
```bash
# Optimasi dan cache
php artisan optimize
php artisan optimize:clear

# Jalankan queue lokal (untuk generate varian gambar)
php artisan queue:work

# Jalankan test
php artisan test
```

---

## Panduan Deploy
1. Pastikan `.env` sudah dikonfigurasi sesuai server produksi.
2. Jalankan perintah build & optimasi:
   ```bash
   npm run build
   php artisan migrate --force
   php artisan db:seed --force   # opsional, hanya jika ingin refresh data
   php artisan optimize
   ```
3. Pastikan folder `storage` dan `bootstrap/cache` writable oleh web server.
4. Atur scheduler & queue worker jika ingin statistik realtime.

Contoh konfigurasi Nginx tersedia di bagian instalasi.

---

## Lisensi
Proyek ini dirilis di bawah lisensi [MIT](https://opensource.org/licenses/MIT).

---

**DPP Partai Gerindra** — Gerindra Web Management System
