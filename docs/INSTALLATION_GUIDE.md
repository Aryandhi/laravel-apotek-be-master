# Panduan Instalasi Apotek POS

Panduan lengkap untuk menginstal aplikasi Apotek POS di Windows dan MacOS untuk development.

---

## Daftar Isi

1. [Persyaratan Sistem](#persyaratan-sistem)
2. [Instalasi di Windows](#instalasi-di-windows)
3. [Instalasi di MacOS](#instalasi-di-macos)
4. [Konfigurasi Database](#konfigurasi-database)
5. [Setup Project Laravel](#setup-project-laravel)
6. [Menjalankan Aplikasi](#menjalankan-aplikasi)
7. [Troubleshooting](#troubleshooting)

---

## Persyaratan Sistem

### Minimum Requirements
- **PHP**: 8.3 atau lebih baru
- **Composer**: 2.x
- **MySQL/MariaDB**: 8.0+ / 10.4+
- **Node.js**: 18.x atau lebih baru
- **NPM**: 9.x atau lebih baru
- **Git**: 2.x

### Software yang Dibutuhkan

| Windows | MacOS |
|---------|-------|
| Laragon | Herd Laravel |
| TablePlus | TablePlus |
| VS Code | VS Code |
| Git Bash | Terminal |

---

## Instalasi di Windows

### Langkah 1: Install Laragon

Laragon adalah development environment yang mudah digunakan untuk Windows.

1. **Download Laragon**
   - Buka: https://github.com/leokhoa/laragon/releases/download/6.0.0/laragon-wamp.exe
   - Download file installer

2. **Install Laragon**
   - Jalankan file installer yang sudah didownload
   - Pilih lokasi instalasi (default: `C:\laragon`)
   - Klik "Next" dan tunggu proses instalasi selesai

3. **Jalankan Laragon**
   - Buka Laragon dari Start Menu atau Desktop
   - Klik tombol "Start All" untuk menjalankan Apache dan MySQL

### Langkah 2: Setup Environment Variables (PHP)

Agar PHP dapat diakses dari command prompt:

1. **Buka System Properties**
   - Tekan `Windows + R`
   - Ketik `sysdm.cpl` dan tekan Enter
   - Klik tab "Advanced"
   - Klik "Environment Variables"

2. **Edit Path Variable**
   - Di bagian "System variables", cari dan klik "Path"
   - Klik "Edit"
   - Klik "New"
   - Tambahkan: `C:\laragon\bin\php\php-8.3.x-nts-Win32-vs16-x64`
   - (Sesuaikan dengan versi PHP yang terinstall di Laragon)

3. **Verifikasi**
   - Buka Command Prompt baru
   - Ketik: `php -v`
   - Pastikan muncul versi PHP 8.3.x

### Langkah 3: Install Composer

1. **Download Composer**
   - Buka: https://getcomposer.org/download/
   - Download Composer-Setup.exe

2. **Install Composer**
   - Jalankan installer
   - Pilih PHP executable dari Laragon
   - Ikuti wizard instalasi

3. **Verifikasi**
   ```bash
   composer -V
   ```
   Output: `Composer version 2.x.x`

### Langkah 4: Install TablePlus

1. **Download TablePlus**
   - Buka: https://tableplus.com/windows
   - Download installer untuk Windows

2. **Install**
   - Jalankan installer
   - Ikuti wizard instalasi

### Langkah 5: Install VS Code

1. **Download VS Code**
   - Buka: https://code.visualstudio.com/
   - Download installer untuk Windows

2. **Install Extensions**
   Buka VS Code dan install extensions berikut:
   - Laravel Extension Pack
   - PHP Intelephense
   - Laravel Blade Snippets
   - DotENV

### Langkah 6: Install Git

1. **Download Git**
   - Buka: https://git-scm.com/download/win
   - Download installer

2. **Install Git**
   - Jalankan installer
   - Pilih default options
   - Pastikan "Git Bash" terinstall

---

## Instalasi di MacOS

### Langkah 1: Install Herd Laravel

Herd adalah development environment modern untuk Laravel di MacOS.

1. **Download Herd**
   - Buka: https://herd.laravel.com/
   - Klik "Download for macOS"

2. **Install Herd**
   - Buka file `.dmg` yang sudah didownload
   - Drag Herd ke folder Applications
   - Buka Herd dari Applications

3. **Setup Herd**
   - Herd akan otomatis install PHP, Composer, dan tools lainnya
   - PHP 8.3 akan terinstall secara default

4. **Verifikasi**
   ```bash
   php -v
   composer -V
   ```

### Langkah 2: Install MariaDB

Opsi A: Menggunakan Homebrew (Recommended)

1. **Install Homebrew** (jika belum ada)
   ```bash
   /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
   ```

2. **Install MariaDB**
   ```bash
   brew install mariadb
   ```

3. **Start MariaDB**
   ```bash
   brew services start mariadb
   ```

4. **Secure Installation**
   ```bash
   sudo mysql_secure_installation
   ```
   - Set root password
   - Remove anonymous users: Y
   - Disallow root login remotely: Y
   - Remove test database: Y
   - Reload privilege tables: Y

Opsi B: Menggunakan DBngin

1. **Download DBngin**
   - Buka: https://dbngin.com/
   - Download untuk MacOS

2. **Install DBngin**
   - Buka file `.dmg`
   - Drag ke Applications
   - Buka DBngin dan buat database server baru

### Langkah 3: Install TablePlus

1. **Download TablePlus**
   - Buka: https://tableplus.com/
   - Download untuk MacOS

2. **Install**
   - Buka file `.dmg`
   - Drag ke Applications

### Langkah 4: Install VS Code

1. **Download VS Code**
   - Buka: https://code.visualstudio.com/
   - Download untuk MacOS

2. **Install Extensions**
   - Laravel Extension Pack
   - PHP Intelephense
   - Laravel Blade Snippets
   - DotENV

### Langkah 5: Install Node.js

```bash
brew install node
```

Verifikasi:
```bash
node -v
npm -v
```

---

## Konfigurasi Database

### Buat Database

1. **Buka TablePlus**
2. **Buat Koneksi Baru**
   - Klik "+" atau "Create new connection"
   - Pilih MySQL/MariaDB
   - Isi detail:
     - **Name**: Apotek POS
     - **Host**: 127.0.0.1
     - **Port**: 3306
     - **User**: root
     - **Password**: (password yang sudah diset)
   - Klik "Test" untuk memastikan koneksi berhasil
   - Klik "Connect"

3. **Buat Database**
   ```sql
   CREATE DATABASE apotek_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

---

## Setup Project Laravel

### Langkah 1: Clone Repository

```bash
# Windows (Git Bash) / MacOS (Terminal)
cd ~/development
git clone https://github.com/[username]/laravel-apotik-be.git
cd laravel-apotik-be
```

### Langkah 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Langkah 3: Setup Environment

1. **Copy file .env**
   ```bash
   cp .env.example .env
   ```

2. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

3. **Edit file .env**
   ```env
   APP_NAME="Apotek POS"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=apotek_pos
   DB_USERNAME=root
   DB_PASSWORD=password_anda
   ```

### Langkah 4: Migrasi Database

```bash
# Jalankan migrasi
php artisan migrate

# Jalankan seeder (data awal)
php artisan db:seed
```

### Langkah 5: Setup Storage Link

```bash
php artisan storage:link
```

### Langkah 6: Build Assets

```bash
npm run build
```

---

## Menjalankan Aplikasi

### Development Mode

1. **Jalankan Laravel Server**
   ```bash
   php artisan serve
   ```
   Aplikasi berjalan di: http://localhost:8000

2. **Jalankan Vite (untuk hot reload CSS/JS)**
   ```bash
   npm run dev
   ```

3. **Atau jalankan sekaligus (jika tersedia)**
   ```bash
   composer run dev
   ```

### Akses Aplikasi

| URL | Keterangan |
|-----|------------|
| http://localhost:8000 | Landing Page |
| http://localhost:8000/admin | Filament Admin Panel |
| http://localhost:8000/pos | POS (Point of Sale) |

### Default Login (Setelah Seeder)

```
Admin Panel:
Email: admin@apotek.com
Password: password

POS:
Email: kasir@apotek.com
Password: password
```

---

## Troubleshooting

### Error: "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest"

**Solusi:**
```bash
npm run build
```

### Error: "SQLSTATE[HY000] [2002] Connection refused"

**Solusi:**
1. Pastikan MySQL/MariaDB sudah running
2. Periksa konfigurasi database di `.env`
3. Windows: Buka Laragon dan klik "Start All"
4. MacOS: `brew services start mariadb`

### Error: "Class not found"

**Solusi:**
```bash
composer dump-autoload
php artisan cache:clear
php artisan config:clear
```

### Error: "Permission denied" di MacOS

**Solusi:**
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:staff storage bootstrap/cache
```

### Error: "The mix manifest does not exist"

**Solusi:**
```bash
npm install
npm run build
```

### PHP Extension Missing

**Windows (Laragon):**
1. Buka Laragon
2. Menu → PHP → Extensions
3. Enable extension yang dibutuhkan

**MacOS (Herd):**
Herd secara otomatis menginstall extensions yang umum digunakan.

### Port 8000 Already in Use

**Solusi:**
```bash
# Gunakan port lain
php artisan serve --port=8001

# Atau hentikan proses yang menggunakan port 8000
# Windows:
netstat -ano | findstr :8000
taskkill /PID [PID] /F

# MacOS:
lsof -i :8000
kill -9 [PID]
```

---

## Tips Development

### 1. Gunakan Tinker untuk Testing

```bash
php artisan tinker
```

```php
// Contoh query
App\Models\Product::count();
App\Models\User::first();
```

### 2. Fresh Database

```bash
php artisan migrate:fresh --seed
```

### 3. Clear All Cache

```bash
php artisan optimize:clear
```

### 4. Generate IDE Helper (Optional)

```bash
composer require --dev barryvdh/laravel-ide-helper
php artisan ide-helper:generate
php artisan ide-helper:models
```

---

## Struktur Folder Penting

```
laravel-apotik-be/
├── app/
│   ├── Filament/          # Admin Panel Resources
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/       # API Controllers
│   │   │   └── Pos/       # POS Controllers
│   │   └── Requests/      # Form Requests
│   ├── Models/            # Eloquent Models
│   └── Services/          # Business Logic
├── config/                # Konfigurasi
├── database/
│   ├── migrations/        # Database Migrations
│   └── seeders/           # Data Seeders
├── resources/
│   ├── views/             # Blade Templates
│   └── js/                # JavaScript
├── routes/
│   ├── api.php            # API Routes
│   └── web.php            # Web Routes
└── storage/               # File Storage
```

---

## Selanjutnya

Setelah instalasi berhasil, Anda dapat:

1. **Mengakses Admin Panel** di `/admin` untuk manajemen data master
2. **Mengakses POS** di `/pos` untuk transaksi penjualan
3. **Membaca dokumentasi fitur** di `docs/FEATURES.md`
4. **Deploy ke production** menggunakan panduan di `docs/VPS_DEPLOYMENT.md`
