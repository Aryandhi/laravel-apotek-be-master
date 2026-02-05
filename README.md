# Apotek POS - Backend API & Admin Panel

Sistem Point of Sale (POS) untuk Apotek dengan fitur lengkap manajemen inventori, penjualan, pembelian, dan laporan. Dibangun dengan Laravel 12 dan Filament 4 untuk admin panel.

## Fitur Utama

### Admin Panel (Filament)
- **Dashboard** - Ringkasan penjualan, stok, dan statistik harian
- **Data Master** - Kategori, Unit, Produk, Supplier, Pelanggan, Dokter
- **Inventori** - Manajemen stok, batch, stock opname, pergerakan stok
- **Transaksi** - Penjualan, Pembelian, Retur, Shift Kasir
- **Laporan** - Penjualan, Pembelian, Stok, Rugi Laba, Produk Terlaris (Export Excel/PDF)
- **Pengaturan** - User, Role & Permission, Toko, Metode Pembayaran, Xendit Payment, Pengaturan Inventori

### Fitur Unggulan
- **Multi-Batch Stock Allocation** - Otomatis alokasi stok dari beberapa batch (FEFO/FIFO)
- **Auto Stock Sync** - Otomatis sinkron stok dari pembelian saat status "Diterima"
- **Xendit Payment Integration** - Pembayaran digital via Xendit (QRIS, VA, E-Wallet)
- **POS dengan Cart Sharing** - Keranjang tersinkron antara halaman Kasir dan Cari Produk
- **Role-Based Access Control** - Hak akses granular per modul

### REST API (untuk Flutter/Mobile App)
- Authentication dengan Laravel Sanctum
- CRUD untuk semua entitas
- Transaksi penjualan & pembelian
- Integrasi pembayaran Xendit
- Laporan & statistik

## Tech Stack

- **Framework**: Laravel 12
- **PHP**: 8.3+
- **Database**: MySQL 8.0+
- **Admin Panel**: Filament 4
- **Authentication**: Laravel Sanctum
- **Role & Permission**: Spatie Laravel Permission
- **Payment Gateway**: Xendit SDK
- **Export**: Maatwebsite Excel, DomPDF
- **Activity Logging**: Spatie Activity Log

## Persyaratan Sistem

- PHP >= 8.3
- Composer >= 2.0
- MySQL >= 8.0
- Node.js >= 18 (untuk build assets)
- NPM >= 9

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/bahrie127/laravel-apotek-be.git
cd laravel-apotek-be
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apotek_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Setup Database

```bash
# Buat database baru
mysql -u root -p -e "CREATE DATABASE apotek_db"

# Jalankan migrasi dan seeder
php artisan migrate:fresh --seed
```

### 5. Build Assets

```bash
npm run build
```

### 6. Jalankan Aplikasi

```bash
php artisan serve
```

Akses aplikasi di: `http://localhost:8000/admin`

## Akun Default

Setelah menjalankan seeder, akun berikut tersedia:

| Email | Password | Role | Akses |
|-------|----------|------|-------|
| superadmin@apotek.com | password | Super Admin | Full akses semua fitur |
| owner@apotek.com | password | Owner | Full akses semua fitur |
| admin@apotek.com | password | Admin | Full akses semua fitur |
| apoteker@apotek.com | password | Apoteker | Produk, stok, penjualan, pembelian |
| kasir@apotek.com | password | Kasir | POS, penjualan, shift kasir |
| asisten@apotek.com | password | Asisten | View only (terbatas) |

## Data Seeder

Seeder menyediakan data lengkap untuk testing:

| Data | Jumlah | Keterangan |
|------|--------|------------|
| Category Types | 10 | Obat Bebas, Obat Keras, Alkes, dll |
| Categories | 34 | Antibiotik, Vitamin, dll |
| Units | 20 | Tablet, Kapsul, Botol, dll |
| Suppliers | 10 | Kimia Farma, Kalbe, dll |
| Products | 50 | Produk dengan harga & stok |
| Product Batches | 150 | 3 batch per produk |
| Customers | 10 | Pelanggan dengan alamat & telepon |
| Doctors | 10 | Dokter dengan spesialisasi & SIP |
| Payment Methods | 4 | Cash, Transfer, QRIS, Xendit |
| Users | 6 | Dengan role masing-masing |
| Roles | 6 | Super Admin, Owner, Admin, Apoteker, Kasir, Asisten |
| Permissions | 55+ | Hak akses granular per modul |

## Struktur Kategori Produk

```
├── Obat Bebas (Hijau)
│   ├── Obat Batuk, Obat Maag, Obat Demam, dll
├── Obat Bebas Terbatas (Biru)
│   ├── Obat Flu, Obat Alergi, Obat Mata, dll
├── Obat Keras (Merah) - Butuh Resep
│   ├── Antibiotik, Antihipertensi, Antidiabetes, dll
├── Narkotika & Psikotropika
│   ├── Narkotika Gol II/III, Psikotropika Gol IV
├── Alat Kesehatan
│   ├── Alat Tes, Masker, Perban, dll
├── Kosmetik
│   ├── Perawatan Kulit, Rambut, Tubuh
├── Suplemen
│   ├── Vitamin & Suplemen
├── Obat Tradisional
│   ├── Jamu, Herbal, Fitofarmaka
└── Lainnya
    ├── Susu & Nutrisi, Perlengkapan Bayi
```

## API Endpoints

### Authentication
```
POST   /api/login              # Login
POST   /api/register           # Register
POST   /api/logout             # Logout
GET    /api/user               # Get current user
```

### Master Data
```
GET    /api/categories         # List kategori
GET    /api/category-types     # List tipe kategori
GET    /api/products           # List produk
GET    /api/products/{id}      # Detail produk
GET    /api/units              # List satuan
GET    /api/suppliers          # List supplier
GET    /api/customers          # List pelanggan
GET    /api/doctors            # List dokter
GET    /api/payment-methods    # List metode pembayaran
```

### Transaksi
```
POST   /api/sales              # Buat penjualan
GET    /api/sales              # List penjualan
GET    /api/sales/{id}         # Detail penjualan
POST   /api/purchases          # Buat pembelian
GET    /api/purchases          # List pembelian
```

### Laporan
```
GET    /api/reports/sales      # Laporan penjualan
GET    /api/reports/stock      # Laporan stok
GET    /api/reports/expiring   # Produk mendekati expired
```

## Konfigurasi Xendit (Opsional)

Xendit dapat dikonfigurasi melalui 2 cara:

### Via Admin Panel (Recommended)
1. Login ke admin panel
2. Buka menu **Pengaturan > Xendit Payment**
3. Masukkan Secret Key dan Webhook Token
4. Klik **Test Koneksi** untuk verifikasi
5. Klik **Simpan**

### Via Environment File
Tambahkan di `.env`:

```env
XENDIT_ENABLED=true
XENDIT_SECRET_KEY=xnd_development_xxxxx
XENDIT_WEBHOOK_TOKEN=your_webhook_token
XENDIT_IS_PRODUCTION=false
```

> **Note**: Pengaturan via Admin Panel disimpan di database dan akan override konfigurasi `.env`

## Menjalankan di Production

### 1. Server Requirements
- PHP 8.3+ dengan extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- MySQL 8.0+
- Nginx atau Apache
- SSL Certificate (untuk Xendit webhook)

### 2. Deployment

```bash
cd /var/www/laravel-apotek-be
git pull origin master
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan db:seed --force  # Jika perlu update data master
```

### 3. Optimasi Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache
php artisan filament:cache-components
```

### 4. Set Environment

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

### 5. Setup Queue (Opsional)

```bash
php artisan queue:work --daemon
```

### 6. Update Setelah Deploy

```bash
git pull origin master
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
npm run build
```

## Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan test tertentu
php artisan test --filter=ProductTest
```

## Troubleshooting

### Menu tidak muncul setelah login
```bash
php artisan permission:cache-reset
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=UserSeeder
```

### Error Vite manifest
```bash
npm run build
```

### Clear semua cache
```bash
php artisan optimize:clear
```

### Error SQL GROUP BY (only_full_group_by)
Aplikasi sudah dioptimasi untuk MySQL strict mode. Jika masih error:
```bash
php artisan cache:clear
```

### Xendit Settings tidak bisa disimpan
Pengaturan Xendit disimpan di database, bukan `.env`. Pastikan tabel `settings` ada:
```bash
php artisan migrate
```

### Permission denied saat upload file
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Kontribusi

1. Fork repository
2. Buat branch fitur (`git checkout -b fitur-baru`)
3. Commit perubahan (`git commit -m 'Tambah fitur baru'`)
4. Push ke branch (`git push origin fitur-baru`)
5. Buat Pull Request

## Lisensi

Project ini dilisensikan di bawah [MIT License](LICENSE).

## Kontak

- **Author**: Bahri
- **GitHub**: [@bahrie127](https://github.com/bahrie127)
