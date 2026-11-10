# PetCareOps

<p align="center">
  <strong>Sistem Pengelolaan Operasional Layanan Pet Grooming</strong>
</p>

PetCareOps adalah aplikasi web untuk membantu pengelolaan operasional layanan pet grooming. Aplikasi ini mencakup pengelolaan pelanggan dan hewan peliharaan, layanan grooming, estimasi durasi, penjadwalan appointment, pengelolaan groomer, pencatatan hasil grooming, invoice, pembayaran, dan pengingat grooming berkala.

**Live Demo:** https://petcareops-production.up.railway.app

## Teknologi

- Laravel 12
- PHP 8.2
- PostgreSQL
- Supabase
- Laravel Breeze
- Spatie Laravel Permission
- Tailwind CSS
- Vite
- PHPUnit

## Persyaratan

Pastikan perangkat sudah memiliki:

- PHP 8.2 atau lebih baru
- Composer
- Node.js dan npm
- Git
- Akses ke database PostgreSQL Supabase

## Instalasi

### 1. Clone Repository

```bash
git clone <URL_REPOSITORY>
cd petcareops
```

### 2. Install Dependency

```bash
composer install
npm install
```

### 3. Konfigurasi Environment

Salin file `.env.example` menjadi `.env`.

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Linux/macOS:

```bash
cp .env.example .env
```

### 4. Konfigurasi Database

Sesuaikan konfigurasi database pada file `.env` menggunakan kredensial Supabase masing-masing.

```env
APP_NAME=PetCareOps
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql
DB_HOST=your-supabase-pooler-host
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=your-database-username
DB_PASSWORD=your-database-password
```

Jangan menggunakan atau membagikan kredensial database milik anggota tim lain.

### 5. Konfigurasi Password Seeder

Akun demo dibuat melalui `UserSeeder`. Password akun diambil dari konfigurasi `config/petcareops.php` dan environment lokal.

Pastikan seluruh password akun seeder sudah diatur sebelum menjalankan seeder. Jangan menyimpan password asli di repository.

### 6. Generate Application Key

```bash
php artisan key:generate
```

### 7. Jalankan Migration dan Seeder

```bash
php artisan migrate --seed
```

Perintah tersebut akan membuat struktur database dan mengisi data konfigurasi, akun staf, faktor estimasi durasi, data groomer, serta data demonstrasi.

**Catatan Supabase:**

Migration Row Level Security (RLS) menggunakan role PostgreSQL `postgres`. Pastikan akun database yang digunakan memiliki keanggotaan dan izin untuk menjalankan `SET ROLE postgres`. Kebutuhan role dan kepemilikan tabel perlu diperiksa jika menggunakan project Supabase baru.

RLS diaktifkan pada tabel-tabel dalam schema `public`. Akses aplikasi tetap dikendalikan melalui autentikasi, middleware, dan otorisasi Laravel. Jangan menambahkan policy publik untuk membuka data pelanggan, hewan peliharaan, appointment, atau pembayaran tanpa kebutuhan dan tinjauan keamanan.

### 8. Build Frontend

```bash
npm run build
```

### 9. Jalankan Aplikasi

```bash
php artisan serve
```

Buka aplikasi melalui:

```text
http://127.0.0.1:8000
```

Untuk pengembangan frontend, jalankan Vite pada terminal terpisah:

```bash
npm run dev
```

## Akun Demo

Akun berikut disediakan oleh `UserSeeder`:

| Role | Email |
|---|---|
| Admin | admin@petcareops.test |
| Resepsionis | receptionist@petcareops.test |
| Groomer 1 | groomer1@petcareops.test |
| Groomer 2 | groomer2@petcareops.test |
| Groomer 3 | groomer3@petcareops.test |

Password mengikuti konfigurasi lokal `config/petcareops.php` dan environment yang digunakan. Tidak ada password default yang dicantumkan dalam repository.

## Data Demonstrasi

`DemoSeeder` menyediakan data demonstrasi berupa:

- 3 pelanggan
- 3 hewan peliharaan
- 3 layanan grooming
- 4 appointment dengan status berbeda
- 2 grooming record
- 2 invoice
- 3 pembayaran
- 2 reminder

Data demonstrasi dibuat untuk membantu pengujian alur aplikasi dan tampilan dashboard.

Untuk menjalankan ulang data demonstrasi:

```bash
php artisan db:seed --class=DemoSeeder
```

Seeder menggunakan identitas data demo agar dapat dijalankan berulang tanpa membuat duplikasi pada data demo yang sama.

**Peringatan:** Jangan menjalankan seeder demo pada database production.

## Pengujian

Jalankan seluruh pengujian otomatis menggunakan:

```bash
php artisan test
```

Audit dependency PHP:

```bash
composer audit
```

Audit dependency JavaScript:

```bash
npm audit
```

## Keamanan dan Konfigurasi Production

Untuk deployment production, pastikan konfigurasi berikut diterapkan:

```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
```

Ketentuan keamanan:

- Gunakan HTTPS.
- Jangan commit file `.env`.
- Gunakan `APP_KEY` khusus untuk environment production.
- Gunakan kredensial database dengan hak akses yang sesuai.
- Jangan menggunakan password akun demonstrasi untuk akun production.
- Pastikan session cookie hanya dikirim melalui koneksi HTTPS.
- Jangan menampilkan detail exception kepada pengguna.
- Tinjau ulang hak akses route dan role sebelum deployment.

## Integrasi WhatsApp

PetCareOps menyediakan integrasi WhatsApp melalui Fonnte secara opsional. Jika token Fonnte tidak tersedia, aplikasi menyediakan fallback berupa tautan WhatsApp (`wa.me`).

Pengiriman otomatis melalui Fonnte hanya dapat digunakan setelah token dan konfigurasi layanan diatur.

## Struktur Pengembangan

Pengembangan backend menggunakan pola Laravel:

- `app/Models`: model dan relasi database.
- `app/Services`: logika bisnis aplikasi.
- `app/Http/Controllers`: pengendali request.
- `app/Http/Requests`: validasi request.
- `app/Http/Middleware`: autentikasi dan pembatasan akses.
- `database/migrations`: perubahan struktur database.
- `database/seeders`: data awal dan data demonstrasi.
- `tests/Feature` dan `tests/Unit`: pengujian otomatis.

## Catatan Pengembangan Tim

- Jangan commit file `.env`.
- Gunakan branch fitur masing-masing untuk pengembangan.
- Jangan mengubah branch `main` secara langsung.
- Jalankan pengujian sebelum membuat pull request.
- Koordinasikan perubahan migration, middleware, model, dan route dengan anggota tim lain.
- Jangan menjalankan `migrate:fresh` pada database bersama karena perintah tersebut menghapus seluruh tabel dan data.

## Kontributor

Terima kasih kepada seluruh anggota tim yang telah berkontribusi dalam pengembangan PetCareOps.

| Nama | Email | Instagram |
|---|---|---|
| Adelia Swastika Dewi | adeliaswastika@gmail.com | (https://www.linkedin.com/in/adeliaswastika/) |
| Devi Atika Putri | viatika265@gmail.com | (https://www.linkedin.com/in/deviatika265/) |

## Lisensi

Proyek PetCareOps dikembangkan untuk keperluan pembelajaran dan pengembangan aplikasi.
