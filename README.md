<p align="center">
  <a href="https://news.roemahcita.id" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
<a href="https://github.com/akmaller/Gerindra-Web_Management_System/actions"><img src="https://img.shields.io/github/actions/workflow/status/akmaller/Gerindra-Web_Management_System/laravel.yml?branch=main" alt="Build Status"></a>
<a href="https://packagist.org/packages/intervention/image"><img src="https://img.shields.io/packagist/dt/intervention/image" alt="Intervention Image Downloads"></a>
<a href="https://github.com/akmaller/Gerindra-Web_Management_System/releases"><img src="https://img.shields.io/github/v/release/akmaller/Gerindra-Web_Management_System" alt="Latest Stable Version"></a>
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/license-MIT-green.svg" alt="License"></a>
</p>

---

## 📰 Gerindra-Web_Management System

**Gerindra-Web_Management System** adalah aplikasi berita berbasis **Laravel 12** dan **Filament v4**, dikembangkan untuk mengelola konten berita, kategori, user, serta mendukung optimasi gambar otomatis (resize, compress, dan generate WebP).

### ✨ Fitur Utama

-   Manajemen berita, kategori, user, dan redaksi dengan **Filament v4**.
-   **Auto compress image** + generate **WebP** untuk performa cepat.
-   Upload image melalui **Filament FileUpload** ke `storage/public`.
-   Responsive frontend (Blade + Alpine.js).
-   Siap untuk deployment di server production.

---

## 🚀 Persiapan Server

Pastikan server memiliki:

-   **PHP 8.3** + Composer
-   **MySQL/MariaDB**
-   Ekstensi PHP: `imagick` atau `gd`, `mbstring`, `curl`, `pdo_mysql`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `zip`, `bcmath`
-   Web server: **Nginx** / **Apache**
-   (Opsional) CLI image optimizer: `jpegoptim`, `optipng`, `pngquant`, `gifsicle`, `webp`

---

## 📦 Instalasi

```bash
git clone https://github.com/akmaller/Gerindra-Web_Management_System.git
cd Gerindra-Web_Management_System
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

Konfigurasi `.env` sesuai environment (DB, APP_URL, dll).

---

## 🗄️ Database

Jalankan migration:

```bash
php artisan migrate --force
```

Jalankan seeder (optional):

```bash
php artisan db:seed
```

Buat user admin Filament:

```bash
php artisan make:filament-user
```

---

## 🔑 Permission

```bash
mkdir -p storage/framework/{cache,sessions,views}
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## 🌐 Konfigurasi Nginx (contoh)

```nginx
server {
    server_name news.roemahcita.id;
    root /www/wwwroot/Gerindra-Web_Management_System/public;

    index index.php index.html;
    client_max_body_size 16m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
}
```

---

## 🔄 Deployment Update

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 📝 License

Project ini dirilis di bawah lisensi [MIT](https://opensource.org/licenses/MIT).

---

## 👤 Author : Akmaller

Dikembangkan oleh **PT. Roemah Cita Kreatif**  
✉️ info@roemahcita.id  
🌐 [roemahcita.id](https://roemahcita.id)
