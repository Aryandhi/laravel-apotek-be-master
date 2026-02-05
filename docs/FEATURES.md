# Dokumentasi Fitur Apotek POS

Dokumentasi lengkap fitur-fitur yang tersedia di aplikasi Apotek POS.

---

## Daftar Isi

1. [Gambaran Umum Sistem](#gambaran-umum-sistem)
2. [Arsitektur Aplikasi](#arsitektur-aplikasi)
3. [Point of Sale (POS)](#point-of-sale-pos)
4. [Manajemen Inventori](#manajemen-inventori)
5. [Manajemen Pembelian](#manajemen-pembelian)
6. [Manajemen Penjualan](#manajemen-penjualan)
7. [Fitur Farmasi](#fitur-farmasi)
8. [Shift Kasir](#shift-kasir)
9. [Pembayaran & Xendit](#pembayaran--xendit)
10. [Laporan & Analitik](#laporan--analitik)
11. [Manajemen User & Akses](#manajemen-user--akses)
12. [API untuk Mobile/Flutter](#api-untuk-mobileflutter)
13. [Admin Panel (Filament)](#admin-panel-filament)

---

## Gambaran Umum Sistem

Apotek POS adalah sistem manajemen apotek modern yang mencakup:

- **Point of Sale (POS)**: Transaksi penjualan dengan fitur lengkap
- **Inventory Management**: Manajemen stok dengan batch dan expiry date
- **Purchase Management**: Manajemen pembelian dari supplier
- **Prescription Handling**: Penanganan resep dokter
- **Reporting**: Laporan penjualan, stok, dan keuangan
- **Mobile API**: API lengkap untuk integrasi Flutter/mobile

### Teknologi yang Digunakan

| Komponen | Teknologi |
|----------|-----------|
| Backend | Laravel 12, PHP 8.3 |
| Admin Panel | Filament v3 |
| Database | MySQL/MariaDB |
| API Auth | Laravel Sanctum |
| Payment Gateway | Xendit |
| Queue | Database Queue |

---

## Arsitektur Aplikasi

### Struktur Folder Utama

```
app/
├── Enums/              # Status dan Type enums
├── Filament/           # Admin Panel Resources
│   ├── Pages/          # Custom pages (Reports, Settings)
│   ├── Resources/      # CRUD Resources
│   └── Widgets/        # Dashboard widgets
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/     # API Controllers
│   │   └── Pos/        # POS Web Controllers
│   └── Requests/       # Form Request Validation
├── Models/             # 34 Eloquent Models
├── Services/           # Business Logic Services
└── Traits/             # Reusable Traits (LogsActivity)
```

### Entitas Database Utama

```
┌─────────────────────────────────────────────────────────────────┐
│                        MASTER DATA                               │
├─────────────────────────────────────────────────────────────────┤
│ Store          │ User           │ Supplier       │ Customer      │
│ Category       │ CategoryType   │ Unit           │ Doctor        │
│ PaymentMethod  │ Setting        │ Role/Permission                │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                        INVENTORY                                 │
├─────────────────────────────────────────────────────────────────┤
│ Product        │ ProductBatch   │ UnitConversion │ StockMovement │
│ StockOpname    │ StockOpnameItem│                                │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                      TRANSAKSI                                   │
├─────────────────────────────────────────────────────────────────┤
│ Sale           │ SaleItem       │ SalePayment    │ SaleReturn    │
│ Purchase       │ PurchaseItem   │ PurchasePayment│ PurchaseReturn│
│ CashierShift   │ CashMovement   │ SalePrescription               │
│ XenditTransaction              │ CompoundedItem                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## Point of Sale (POS)

### URL Akses
- **Web POS**: `/pos`
- **Login POS**: `/pos/login`

### Alur Transaksi

```
1. Buka Shift     2. Cari Produk    3. Pilih Batch    4. Tambah ke Cart
     │                  │                 │                  │
     ▼                  ▼                 ▼                  ▼
┌─────────┐      ┌───────────┐     ┌───────────┐      ┌───────────┐
│ Opening │      │ Search by │     │ FEFO Order│      │ Set Qty & │
│ Cash    │      │ Name/Code │     │ (Expiry)  │      │ Discount  │
└─────────┘      │ /Barcode  │     └───────────┘      └───────────┘
                 └───────────┘
     │                                                      │
     ▼                                                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                      5. CHECKOUT                                 │
├─────────────────────────────────────────────────────────────────┤
│  - Pilih Customer (opsional)                                    │
│  - Pilih Payment Method (Cash/Card/E-Wallet/Xendit)             │
│  - Input jumlah bayar                                           │
│  - Proses pembayaran                                            │
└─────────────────────────────────────────────────────────────────┘
     │
     ▼
┌─────────────────────────────────────────────────────────────────┐
│                      6. RECEIPT                                  │
├─────────────────────────────────────────────────────────────────┤
│  - Print receipt (Browser/Thermal Printer)                      │
│  - ESC-POS format untuk thermal printer                         │
│  - Stok otomatis berkurang                                      │
└─────────────────────────────────────────────────────────────────┘
```

### Fitur POS

| Fitur | Deskripsi |
|-------|-----------|
| **Pencarian Produk** | Cari berdasarkan nama, kode, atau barcode |
| **Pemilihan Batch** | FEFO (First Expiry First Out) - prioritas batch yang akan expired duluan |
| **Multiple Payment** | Kombinasi beberapa metode pembayaran |
| **Customer Points** | Sistem poin loyalitas pelanggan |
| **Prescription Flag** | Penandaan item resep |
| **Thermal Printing** | Print receipt ke printer thermal (ESC-POS) |
| **Void Transaction** | Pembatalan transaksi dengan alasan |

### Controller: `TransactionController`

```php
// Endpoint utama
GET  /pos/transactions/create  // Form transaksi baru
POST /pos/transactions         // Proses transaksi
GET  /pos/transactions/{id}    // Detail transaksi
```

---

## Manajemen Inventori

### Product (Produk)

Setiap produk memiliki atribut:

| Field | Deskripsi |
|-------|-----------|
| `code` | Kode produk unik |
| `barcode` | Barcode untuk scanning |
| `kfa_code` | Kode Formularium Nasional |
| `name` | Nama produk/obat |
| `generic_name` | Nama generik |
| `category_id` | Kategori produk |
| `base_unit_id` | Satuan dasar |
| `purchase_price` | Harga beli |
| `selling_price` | Harga jual |
| `min_stock` | Stok minimum (alert) |
| `max_stock` | Stok maksimum |
| `rack_location` | Lokasi rak |
| `requires_prescription` | Wajib resep (boolean) |

### Product Batch (Batch Produk)

Setiap produk dapat memiliki multiple batch:

| Field | Deskripsi |
|-------|-----------|
| `batch_number` | Nomor batch dari pabrik |
| `expired_date` | Tanggal kadaluarsa |
| `stock` | Stok tersedia |
| `initial_stock` | Stok awal saat pembelian |
| `status` | Active, Expired, Damaged, NearExpired |

**FEFO (First Expiry First Out)**:
- Sistem otomatis memprioritaskan batch yang akan expired duluan
- Alert untuk produk mendekati expired (30, 60, 90 hari)
- Status otomatis berubah saat expired

### Stock Movement (Pergerakan Stok)

Setiap perubahan stok tercatat dengan detail:

| Type | Deskripsi |
|------|-----------|
| `Purchase` | Penambahan dari pembelian |
| `Sale` | Pengurangan dari penjualan |
| `ReturnCustomer` | Penambahan dari retur pelanggan |
| `ReturnSupplier` | Pengurangan dari retur ke supplier |
| `AdjustmentIn` | Penyesuaian tambah (stock opname) |
| `AdjustmentOut` | Penyesuaian kurang (stock opname) |
| `Damaged` | Pengurangan karena rusak |
| `Expired` | Pengurangan karena expired |

### Stock Opname (Penghitungan Fisik)

Workflow stock opname:

```
1. Draft         2. In Progress   3. Pending Approval   4. Approved
     │                  │                  │                  │
     ▼                  ▼                  ▼                  ▼
┌─────────┐      ┌───────────┐     ┌───────────┐      ┌───────────┐
│ Buat    │      │ Input     │     │ Review    │      │ Stok      │
│ Session │  →   │ Stok      │  →  │ Selisih   │  →   │ Disesuai- │
│         │      │ Fisik     │     │           │      │ kan       │
└─────────┘      └───────────┘     └───────────┘      └───────────┘
```

Fitur:
- Perbandingan stok sistem vs stok fisik
- Kalkulasi selisih otomatis
- Approval workflow
- Stock movement otomatis saat approved

---

## Manajemen Pembelian

### Purchase Order (Pembelian)

| Field | Deskripsi |
|-------|-----------|
| `invoice_number` | Nomor faktur pembelian |
| `supplier_id` | Supplier/vendor |
| `date` | Tanggal pembelian |
| `due_date` | Jatuh tempo pembayaran |
| `status` | Draft, Received, Partially Paid, Paid |
| `subtotal` | Total sebelum diskon |
| `discount` | Diskon pembelian |
| `tax` | Pajak |
| `total` | Total akhir |
| `paid_amount` | Jumlah yang sudah dibayar |

### Purchase Item

Setiap item pembelian:
- Batch number (untuk tracking)
- Expired date
- Quantity ordered vs received
- Harga beli dan harga jual

### Purchase Return

Proses retur ke supplier:
1. Pilih purchase order
2. Pilih item yang akan diretur
3. Input alasan retur
4. Submit untuk approval
5. Stok dikurangi setelah approved

---

## Manajemen Penjualan

### Sale (Transaksi Penjualan)

| Field | Deskripsi |
|-------|-----------|
| `invoice_number` | Nomor invoice (auto-generate) |
| `customer_id` | Pelanggan (opsional) |
| `doctor_id` | Dokter (untuk resep) |
| `prescription_number` | Nomor resep |
| `is_prescription` | Flag transaksi resep |
| `patient_name` | Nama pasien |
| `date` | Tanggal transaksi |
| `subtotal` | Total sebelum diskon |
| `discount` | Diskon |
| `tax` | Pajak |
| `total` | Total akhir |
| `paid_amount` | Jumlah dibayar |
| `change_amount` | Kembalian |
| `status` | Completed, Draft, Voided, Returned |
| `shift_id` | Shift kasir |

### Sale Status

```
┌──────────┐     ┌──────────┐     ┌──────────┐
│  Draft   │ ──► │Completed │ ──► │ Returned │
└──────────┘     └──────────┘     └──────────┘
                      │
                      ▼
                ┌──────────┐
                │  Voided  │
                └──────────┘
```

### Sale Return (Retur Pelanggan)

Fitur retur:
- Partial return (sebagian item)
- Full return (seluruh transaksi)
- Refund method: Cash, Credit, Check
- Stok otomatis bertambah kembali
- Riwayat retur tercatat

---

## Fitur Farmasi

### Prescription Handling (Resep Dokter)

Model `SalePrescription`:

| Field | Deskripsi |
|-------|-----------|
| `prescription_number` | Nomor resep |
| `doctor_id` | Dokter yang menulis resep |
| `patient_name` | Nama pasien |
| `patient_age` | Usia pasien |
| `patient_address` | Alamat pasien |
| `diagnosis` | Diagnosis |
| `date` | Tanggal resep |
| `is_copy` | Apakah salinan resep |
| `copy_number` | Nomor salinan |

### Category Type (Jenis Kategori)

Klasifikasi produk farmasi:

| Field | Deskripsi |
|-------|-----------|
| `requires_prescription` | Wajib resep |
| `is_narcotic` | Obat narkotika/psikotropika |
| `color` | Warna label |

Contoh kategori:
- **Obat Bebas** (hijau) - tidak perlu resep
- **Obat Bebas Terbatas** (biru) - tidak perlu resep, ada peringatan
- **Obat Keras** (merah) - wajib resep
- **Narkotika** (merah) - wajib resep, kontrol ketat

### Compounded Medication (Racikan)

Model `CompoundedItem`:

| Field | Deskripsi |
|-------|-----------|
| `name` | Nama racikan |
| `type` | Jenis: Liquid, Powder, Capsule, Ointment, Syrup |
| `quantity` | Jumlah |
| `instructions` | Aturan pakai |

Detail bahan:
- Product yang digunakan
- Quantity per bahan
- Harga per bahan
- Unit/satuan

---

## Shift Kasir

### Cashier Shift

Model `CashierShift`:

| Field | Deskripsi |
|-------|-----------|
| `user_id` | Kasir |
| `opening_time` | Waktu buka shift |
| `closing_time` | Waktu tutup shift |
| `opening_cash` | Kas awal |
| `expected_cash` | Kas yang diharapkan |
| `actual_cash` | Kas aktual (hitung manual) |
| `difference` | Selisih |
| `status` | Open, Closed, Balanced |

### Workflow Shift

```
┌────────────────────────────────────────────────────────────────┐
│                      BUKA SHIFT                                 │
│  - Input kas awal (opening cash)                               │
│  - Mulai transaksi                                             │
└────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌────────────────────────────────────────────────────────────────┐
│                    SELAMA SHIFT                                 │
│  - Transaksi penjualan                                         │
│  - Cash movement (kas masuk/keluar)                            │
│  - Laporan real-time                                           │
└────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌────────────────────────────────────────────────────────────────┐
│                     TUTUP SHIFT                                 │
│  - Hitung kas fisik                                            │
│  - Input actual cash                                           │
│  - Sistem hitung expected cash                                 │
│  - Tampilkan selisih                                           │
│  - Print laporan shift                                         │
└────────────────────────────────────────────────────────────────┘
```

### Cash Movement

Pencatatan kas masuk/keluar di luar transaksi:

| Type | Contoh |
|------|--------|
| `in` | Setoran tambahan, penjualan non-sistem |
| `out` | Pembelian perlengkapan, pengeluaran operasional |

### Kalkulasi Expected Cash

```
Expected Cash = Opening Cash
              + Total Cash Sales
              + Total Cash In
              - Total Cash Out
```

---

## Pembayaran & Xendit

### Payment Method

Metode pembayaran yang didukung:

| Method | Tipe | Deskripsi |
|--------|------|-----------|
| Cash | is_cash=true | Tunai |
| Bank Transfer | is_cash=false | Transfer bank |
| Debit Card | is_cash=false | Kartu debit |
| Credit Card | is_cash=false | Kartu kredit |
| E-Wallet | is_cash=false | GoPay, OVO, Dana, dll (via Xendit) |

### Xendit Integration

Model `XenditTransaction`:

| Field | Deskripsi |
|-------|-----------|
| `sale_id` | Transaksi terkait |
| `external_id` | ID transaksi internal |
| `xendit_id` | ID dari Xendit |
| `invoice_url` | URL checkout Xendit |
| `payment_method` | VA, Bank, Card, E-Wallet |
| `payment_channel` | BCA, Mandiri, GoPay, dll |
| `amount` | Nominal pembayaran |
| `status` | Pending, Paid, Settled, Expired, Failed |
| `paid_at` | Waktu pembayaran |
| `expires_at` | Waktu kadaluarsa |

### Alur Pembayaran Xendit

```
┌─────────┐     ┌─────────┐     ┌─────────┐     ┌─────────┐
│ Create  │     │ Customer│     │ Xendit  │     │ Update  │
│ Invoice │ ──► │ Pays    │ ──► │ Webhook │ ──► │ Status  │
└─────────┘     └─────────┘     └─────────┘     └─────────┘
```

### API Endpoints Xendit

```php
GET  /api/v1/xendit/settings        // Get settings
POST /api/v1/xendit/settings/test   // Test connection
POST /api/v1/xendit/invoice         // Create invoice
POST /api/v1/xendit/sale            // Create sale with payment
GET  /api/v1/xendit/check/{id}      // Check payment status
POST /api/v1/xendit/cancel/{id}     // Cancel payment
POST /webhook/xendit/invoice        // Webhook handler
```

---

## Laporan & Analitik

### Jenis Laporan

#### 1. Sales Report (Laporan Penjualan)

Informasi yang ditampilkan:
- Total transaksi
- Total penjualan (revenue)
- Total diskon
- Rata-rata per transaksi
- Breakdown per hari
- Top 10 produk terlaris
- Breakdown per metode pembayaran

Filter:
- Rentang tanggal
- User/kasir
- Status transaksi

#### 2. Stock Report (Laporan Stok)

Informasi yang ditampilkan:
- Stok per produk
- Stok per batch
- Produk dengan stok rendah (di bawah minimum)
- Produk mendekati expired
- Produk sudah expired
- Nilai inventori

#### 3. Purchase Report (Laporan Pembelian)

Informasi yang ditampilkan:
- Total pembelian
- Pembelian per supplier
- Pembelian belum dibayar
- Jatuh tempo pembayaran

#### 4. Profit & Loss Report (Laporan Laba Rugi)

Informasi yang ditampilkan:
- Total revenue (penjualan)
- Cost of Goods Sold (HPP)
- Gross profit
- Profit margin

#### 5. Stock Movement Report (Laporan Pergerakan Stok)

Informasi yang ditampilkan:
- Semua pergerakan stok
- Filter by type (Sale, Purchase, Return, dll)
- Audit trail lengkap

#### 6. Top Products Report (Produk Terlaris)

Informasi yang ditampilkan:
- Produk dengan penjualan tertinggi (quantity)
- Produk dengan revenue tertinggi
- Trend penjualan

### Export Format

Semua laporan mendukung:
- **Excel** (.xlsx)
- **PDF**
- **Print** (browser)

---

## Manajemen User & Akses

### User Roles

| Role | Deskripsi | Akses |
|------|-----------|-------|
| `Owner` | Pemilik apotek | Full access |
| `Manager` | Manager operasional | Inventori, laporan, user |
| `Pharmacist` | Apoteker | Resep, inventori, penjualan |
| `Cashier` | Kasir | POS, transaksi |
| `Assistant` | Asisten | Terbatas |

### Permission System

Menggunakan Spatie Permission:

```php
// Contoh permission
- view_product
- create_product
- update_product
- delete_product
- view_sale
- create_sale
- void_sale
- view_report
- access_pos
- manage_prescription
```

### Method Helper pada User

```php
$user->isOwner();           // Cek apakah owner
$user->isPharmacist();      // Cek apakah apoteker
$user->isCashier();         // Cek apakah kasir
$user->canAccessPrescription(); // Cek akses resep
$user->activeShift;         // Get shift aktif
```

### Activity Logging

Semua aksi user tercatat:

| Field | Deskripsi |
|-------|-----------|
| `user_id` | User yang melakukan aksi |
| `action` | Jenis aksi (create, update, delete) |
| `model_type` | Model yang diubah |
| `model_id` | ID record |
| `old_values` | Nilai sebelum perubahan |
| `new_values` | Nilai setelah perubahan |
| `ip_address` | IP address |
| `user_agent` | Browser/device |

---

## API untuk Mobile/Flutter

### Authentication

```bash
# Login
POST /api/v1/login
{
  "email": "user@example.com",
  "password": "password"
}

# Response
{
  "token": "1|xxxxxxxxxxxx",
  "user": { ... }
}
```

Header untuk request berikutnya:
```
Authorization: Bearer 1|xxxxxxxxxxxx
```

### API Endpoints Utama

#### Dashboard
```
GET /api/v1/dashboard/summary      # Ringkasan dashboard
GET /api/v1/dashboard/low-stock    # Produk stok rendah
GET /api/v1/dashboard/expiring     # Produk akan expired
```

#### Shift
```
GET  /api/v1/shift/current         # Shift aktif
POST /api/v1/shift/open            # Buka shift
POST /api/v1/shift/close           # Tutup shift
GET  /api/v1/shift/summary         # Ringkasan shift
GET  /api/v1/shift/sales           # Penjualan di shift
```

#### Products
```
GET  /api/v1/products              # List produk
GET  /api/v1/products/{id}         # Detail produk
POST /api/v1/products/barcode      # Cari by barcode
```

#### Sales
```
GET  /api/v1/sales                 # List penjualan
GET  /api/v1/sales/{id}            # Detail penjualan
POST /api/v1/sales                 # Buat transaksi
POST /api/v1/sales/{id}/void       # Void transaksi
GET  /api/v1/sales/{id}/receipt    # Get receipt
```

#### Master Data
```
GET /api/v1/categories             # Kategori
GET /api/v1/category-types         # Jenis kategori
GET /api/v1/units                  # Satuan
GET /api/v1/payment-methods        # Metode pembayaran
GET /api/v1/doctors                # Dokter
GET /api/v1/customers              # Pelanggan
GET /api/v1/store                  # Info toko
GET /api/v1/settings               # Pengaturan
```

#### Reports
```
GET /api/v1/reports/sales          # Laporan penjualan
```

### Request/Response Format

Semua API menggunakan JSON format.

**Request Body Example (Create Sale)**:
```json
{
  "customer_id": 1,
  "items": [
    {
      "product_id": 10,
      "product_batch_id": 25,
      "quantity": 2,
      "unit_id": 1,
      "price": 15000,
      "discount": 0
    }
  ],
  "payments": [
    {
      "payment_method_id": 1,
      "amount": 30000
    }
  ],
  "discount": 0,
  "notes": "Catatan transaksi"
}
```

**Response Format**:
```json
{
  "success": true,
  "message": "Transaction created successfully",
  "data": {
    "id": 123,
    "invoice_number": "INV-20260111-001",
    ...
  }
}
```

---

## Admin Panel (Filament)

### URL Akses
- **Admin Panel**: `/admin`

### Resources Tersedia (24 total)

#### Data Master
| Resource | Path | Deskripsi |
|----------|------|-----------|
| ProductResource | `/admin/products` | Manajemen produk |
| ProductBatchResource | `/admin/product-batches` | Manajemen batch |
| CategoryResource | `/admin/categories` | Kategori produk |
| CategoryTypeResource | `/admin/category-types` | Jenis kategori |
| SupplierResource | `/admin/suppliers` | Data supplier |
| CustomerResource | `/admin/customers` | Data pelanggan |
| DoctorResource | `/admin/doctors` | Data dokter |
| UnitResource | `/admin/units` | Satuan |
| UnitConversionResource | `/admin/unit-conversions` | Konversi satuan |
| PaymentMethodResource | `/admin/payment-methods` | Metode pembayaran |
| StoreResource | `/admin/stores` | Info toko |
| SettingResource | `/admin/settings` | Pengaturan |

#### Transaksi
| Resource | Path | Deskripsi |
|----------|------|-----------|
| SaleResource | `/admin/sales` | Data penjualan |
| SaleReturnResource | `/admin/sale-returns` | Retur penjualan |
| PurchaseResource | `/admin/purchases` | Data pembelian |
| PurchaseReturnResource | `/admin/purchase-returns` | Retur pembelian |
| CashierShiftResource | `/admin/cashier-shifts` | Shift kasir |

#### Inventori
| Resource | Path | Deskripsi |
|----------|------|-----------|
| StockOpnameResource | `/admin/stock-opnames` | Stock opname |
| StockMovementResource | `/admin/stock-movements` | Pergerakan stok |

#### User & Akses
| Resource | Path | Deskripsi |
|----------|------|-----------|
| UserResource | `/admin/users` | Manajemen user |
| RoleResource | `/admin/roles` | Roles |
| PermissionResource | `/admin/permissions` | Permissions |
| ActivityLogsResource | `/admin/activity-logs` | Audit log |

#### Payment
| Resource | Path | Deskripsi |
|----------|------|-----------|
| XenditTransactionResource | `/admin/xendit-transactions` | Transaksi Xendit |

### Custom Pages

| Page | Path | Deskripsi |
|------|------|-----------|
| Reports | `/admin/reports` | Dashboard laporan |
| SalesReport | `/admin/reports/sales` | Laporan penjualan |
| PurchaseReport | `/admin/reports/purchases` | Laporan pembelian |
| StockReport | `/admin/reports/stock` | Laporan stok |
| TopProductsReport | `/admin/reports/top-products` | Produk terlaris |
| ProfitLossReport | `/admin/reports/profit-loss` | Laba rugi |
| StockMovementReport | `/admin/reports/stock-movement` | Pergerakan stok |
| StoreSettings | `/admin/store-settings` | Pengaturan toko |
| XenditSettings | `/admin/xendit-settings` | Pengaturan Xendit |

---

## Enums & Status

### SaleStatus
```php
enum SaleStatus: string {
    case Completed = 'completed';
    case Draft = 'draft';
    case Voided = 'voided';
    case Returned = 'returned';
}
```

### PurchaseStatus
```php
enum PurchaseStatus: string {
    case Draft = 'draft';
    case Received = 'received';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
}
```

### BatchStatus
```php
enum BatchStatus: string {
    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';
    case Damaged = 'damaged';
    case NearExpired = 'near_expired';
}
```

### ShiftStatus
```php
enum ShiftStatus: string {
    case Open = 'open';
    case Closed = 'closed';
    case Balanced = 'balanced';
}
```

### UserRole
```php
enum UserRole: string {
    case Owner = 'owner';
    case Manager = 'manager';
    case Pharmacist = 'pharmacist';
    case Cashier = 'cashier';
    case Assistant = 'assistant';
}
```

---

## Services

### XenditService

```php
// app/Services/XenditService.php

// Create invoice
$xenditService->createInvoice($sale, $amount, $description);

// Check payment status
$xenditService->checkPaymentStatus($xenditId);

// Test connection
$xenditService->testConnection();
```

### StockOpnameService

```php
// app/Services/StockOpnameService.php

// Approve stock opname
$stockOpnameService->approve($stockOpname, $approvedBy);

// Apply adjustments
$stockOpnameService->applyAdjustments($stockOpname);
```

### ReceiptPdfService

```php
// app/Services/ReceiptPdfService.php

// Generate PDF receipt
$receiptPdfService->generate($sale);
```

---

## Kesimpulan

Apotek POS adalah sistem manajemen apotek yang lengkap dengan fitur:

- **POS System** dengan FEFO batch selection dan multiple payment
- **Inventory Management** dengan batch tracking dan expiry monitoring
- **Prescription Handling** untuk obat-obatan yang memerlukan resep
- **Comprehensive Reporting** untuk analisis bisnis
- **Role-Based Access Control** untuk keamanan
- **API Integration** untuk aplikasi mobile/Flutter
- **Payment Gateway** terintegrasi dengan Xendit
- **Activity Logging** untuk audit trail lengkap

Sistem ini dibangun dengan Laravel 12 dan Filament v3, mengikuti best practices modern PHP development.
