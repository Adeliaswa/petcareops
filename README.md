# PetCareOps

<p align="center">
  <strong>Sistem Pengelolaan Operasional Layanan Pet Grooming</strong>
</p>

PetCareOps adalah aplikasi web untuk membantu pengelolaan operasional layanan pet grooming. Aplikasi ini mencakup pengelolaan pelanggan dan hewan peliharaan, layanan grooming, estimasi durasi, penjadwalan appointment, pengelolaan groomer, pencatatan hasil grooming, invoice, pembayaran, dan pengingat grooming berkala.

**Live Demo:** https://petcareops-production.up.railway.app

## Akses Website dan Jenis Pengguna

PetCareOps menyediakan tiga area akses yang disesuaikan dengan kebutuhan operasional salon grooming, yaitu halaman publik untuk pelanggan, area pengelolaan staf, dan halaman khusus groomer.

### 1. Pelanggan (Public)

Pelanggan dapat mengakses website tanpa perlu melakukan registrasi atau login. Melalui halaman publik, pelanggan dapat:

- Melihat informasi layanan grooming yang tersedia.
- Memilih hewan peliharaan dan layanan yang ingin dipesan.
- Melihat estimasi durasi grooming berdasarkan karakteristik hewan.
- Melihat pilihan jadwal yang tersedia sesuai durasi layanan dan ketersediaan groomer.
- Melakukan pemesanan menggunakan nomor WhatsApp.
- Mendapatkan bukti pemesanan dan tautan untuk memantau status appointment.
- Memantau status grooming tanpa harus login.

### 2. Admin / Owner

Admin merupakan pemilik atau pengelola salon yang memiliki akses untuk mengatur data dan operasional sistem. Admin dapat mengakses fitur:

- Dashboard dan ringkasan operasional.
- Pengelolaan akun staf, termasuk membuat akun, menonaktifkan akun, dan mengatur ulang kata sandi.
- Pengelolaan data pelanggan dan hewan peliharaan.
- Pengelolaan layanan grooming dan harga.
- Pengaturan faktor estimasi durasi grooming.
- Pengelolaan data groomer, keahlian, shift kerja, dan hari libur.
- Melihat daftar appointment dan kalender beban kerja groomer.
- Melihat riwayat grooming hewan peliharaan.
- Mengelola pengingat grooming berkala.

### 3. Resepsionis

Resepsionis bertanggung jawab menangani pemesanan, antrean, dan transaksi pelanggan. Fitur yang dapat diakses meliputi:

- Melihat dan mengelola daftar appointment harian.
- Membuat appointment untuk pelanggan yang datang langsung (walk-in).
- Mengelola data pelanggan dan hewan peliharaan.
- Melihat kalender kerja dan pembagian jadwal groomer.
- Memperbarui status antrean appointment.
- Melakukan pembatalan appointment.
- Melihat riwayat grooming hewan peliharaan.
- Mencatat pembayaran pelanggan.
- Membuat dan mengunduh invoice dalam format PDF.
- Mengelola pengingat grooming dan menyiapkan pesan WhatsApp untuk pelanggan.

### 4. Groomer

Groomer memiliki halaman khusus yang dirancang agar mudah digunakan melalui telepon genggam. Fitur yang tersedia meliputi:

- Melihat daftar tugas grooming yang ditugaskan kepadanya.
- Melihat informasi dan karakteristik hewan yang akan ditangani.
- Membaca catatan khusus, seperti alergi dan perilaku hewan.
- Melihat riwayat perawatan hewan.
- Memperbarui status pengerjaan grooming.
- Mencatat waktu mulai dan selesai pengerjaan.
- Mencatat hasil serta kondisi hewan setelah grooming.

## Fitur Utama PetCareOps

PetCareOps dikembangkan untuk membantu salon grooming mengelola operasional secara lebih terstruktur melalui fitur-fitur berikut.

| Fitur | Deskripsi |
|---|---|
| **Estimasi Durasi Grooming** | Menghitung perkiraan waktu pengerjaan berdasarkan ukuran, jenis bulu, perilaku, dan kondisi hewan, serta faktor tambahan dan waktu pembersihan. |
| **Smart Slot Finder** | Menampilkan jadwal yang sesuai dengan estimasi durasi, jam kerja, keahlian, istirahat, dan hari libur groomer. |
| **Penugasan Groomer Otomatis** | Mempertimbangkan keahlian dan beban kerja groomer agar pembagian tugas lebih merata. |
| **Online Booking** | Memungkinkan pelanggan memesan layanan melalui halaman publik tanpa membuat akun. |
| **Walk-in Booking** | Memungkinkan resepsionis mencatat pemesanan pelanggan yang datang langsung ke salon. |
| **Manajemen Appointment** | Mengelola jadwal, antrean, status pengerjaan, dan pembatalan appointment. |
| **Kalender Beban Kerja** | Menampilkan jadwal appointment setiap groomer dalam tampilan kalender harian. |
| **Manajemen Data Pelanggan dan Hewan** | Menyimpan informasi pemilik, profil hewan, karakteristik, alergi, dan perilaku hewan. |
| **Riwayat Grooming** | Mendokumentasikan hasil perawatan serta perbandingan estimasi dan durasi aktual setiap hewan. |
| **Pengingat Grooming Berkala** | Membantu mengingatkan pelanggan ketika hewan sudah waktunya melakukan grooming kembali melalui WhatsApp atau tautan `wa.me`. |
| **Invoice dan Pembayaran** | Memungkinkan resepsionis mencatat pembayaran dan menghasilkan invoice dalam format PDF. |
| **Manajemen Hak Akses** | Membatasi akses halaman dan fitur berdasarkan peran admin, resepsionis, dan groomer. |

## Akun Demo

Berikut akun staf yang dapat digunakan untuk mencoba fitur berdasarkan masing-masing role pada website PetCareOps.

| Role | Email |
|---|---|
| Admin / Owner | `admin@petcareops.test` |
| Resepsionis | `receptionist@petcareops.test` |
| Groomer 1 | `groomer1@petcareops.test` |
| Groomer 2 | `groomer2@petcareops.test` |
| Groomer 3 | `groomer3@petcareops.test` |

**Catatan:**
- Pelanggan tidak memerlukan akun untuk melakukan pemesanan.
- Gunakan akun di atas hanya untuk keperluan demonstrasi dan pengujian.
- Password akun demo mengikuti konfigurasi yang disediakan untuk lingkungan demo.
- Setiap akun memiliki hak akses sesuai dengan perannya.

## Batasan Fitur Versi Saat Ini

PetCareOps dikembangkan sebagai Minimum Viable Product (MVP) yang berfokus pada operasional inti salon grooming. Beberapa fitur yang belum tersedia pada versi saat ini antara lain:

- Dashboard analitik lanjutan, seperti pendapatan per jam kerja, utilisasi groomer, dan akurasi estimasi.
- Kalibrasi estimasi durasi secara otomatis berdasarkan rata-rata durasi aktual.
- Pembatalan dan penjadwalan ulang appointment secara mandiri oleh pelanggan.
- Pembayaran online melalui payment gateway.
- Unggah foto hewan dan hasil grooming.
- Operasional multi-cabang.
- Integrasi WhatsApp dua arah.

Fitur-fitur tersebut menjadi bagian dari rencana pengembangan lanjutan PetCareOps.
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

Password mengikuti konfigurasi lokal `config/petcareops.php` dan environment yang digunakan.

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
| Adelia Swastika Dewi | adeliaswastika20@gmail.com | (https://www.linkedin.com/in/adeliaswastika/) |
| Devi Atika Putri | viatika265@gmail.com | (https://www.linkedin.com/in/deviatika265/) |

## Lisensi

Proyek PetCareOps dikembangkan untuk keperluan pembelajaran dan pengembangan aplikasi.
