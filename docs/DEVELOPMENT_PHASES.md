# Development Phases - POS Apotek Backend

## Overview
Dokumen ini berisi step-by-step phase untuk development dan manual testing POS Apotek.
Setiap phase memiliki checklist, commands, dan testing scenarios.

---

## PHASE 1: Foundation & Database Setup

### 1.1 Enums (Jalankan Pertama)
```bash
# Buat folder dan file enums
mkdir -p app/Enums
```

**Files to create:**
- [ ] `app/Enums/CategoryType.php`
- [ ] `app/Enums/BatchStatus.php`
- [ ] `app/Enums/PurchaseStatus.php`
- [ ] `app/Enums/SaleStatus.php`
- [ ] `app/Enums/StockMovementType.php`
- [ ] `app/Enums/UserRole.php`
- [ ] `app/Enums/CompoundType.php`
- [ ] `app/Enums/ShiftStatus.php`
- [ ] `app/Enums/StockOpnameStatus.php`

### 1.2 Migrations (Urutan Penting!)

```bash
# Jalankan satu per satu untuk memastikan tidak ada error
```

**Urutan Migration:**

**Batch 1 - Master Tables (Tidak ada FK)**
```bash
php artisan make:migration create_stores_table
php artisan make:migration create_categories_table
php artisan make:migration create_units_table
php artisan make:migration create_suppliers_table
php artisan make:migration create_doctors_table
php artisan make:migration create_customers_table
php artisan make:migration create_payment_methods_table
```

**Batch 2 - Products & Inventory**
```bash
php artisan make:migration create_products_table
php artisan make:migration create_unit_conversions_table
php artisan make:migration create_product_batches_table
php artisan make:migration create_stock_movements_table
```

**Batch 3 - Purchasing**
```bash
php artisan make:migration create_purchases_table
php artisan make:migration create_purchase_items_table
php artisan make:migration create_purchase_payments_table
```

**Batch 4 - Sales / POS**
```bash
php artisan make:migration create_cashier_shifts_table
php artisan make:migration create_cash_movements_table
php artisan make:migration create_sales_table
php artisan make:migration create_sale_items_table
php artisan make:migration create_sale_payments_table
php artisan make:migration create_sale_prescriptions_table
php artisan make:migration create_prescription_items_table
```

**Batch 5 - Compounding (Racikan)**
```bash
php artisan make:migration create_compounded_items_table
php artisan make:migration create_compounded_item_details_table
```

**Batch 6 - Stock Opname & Returns**
```bash
php artisan make:migration create_stock_opnames_table
php artisan make:migration create_stock_opname_items_table
php artisan make:migration create_purchase_returns_table
php artisan make:migration create_purchase_return_items_table
php artisan make:migration create_sale_returns_table
php artisan make:migration create_sale_return_items_table
```

**Batch 7 - Settings & Logs**
```bash
php artisan make:migration create_settings_table
php artisan make:migration create_activity_logs_table
php artisan make:migration add_role_store_to_users_table
```

### 1.3 Manual Testing Phase 1
```bash
# Run migration
php artisan migrate

# Check tables created
php artisan db:show
```

**Test Checklist:**
- [ ] Semua migration berhasil tanpa error
- [ ] Semua tabel terbuat dengan benar
- [ ] Foreign key constraints benar

---

## PHASE 2: Models & Relationships

### 2.1 Models (Urutan sesuai dependency)

**Batch 1 - Core Models (No FK)**
```bash
php artisan make:model Store
php artisan make:model Category
php artisan make:model Unit
php artisan make:model Supplier
php artisan make:model Doctor
php artisan make:model Customer
php artisan make:model PaymentMethod
```

**Batch 2 - Product & Inventory**
```bash
php artisan make:model Product -f
php artisan make:model UnitConversion
php artisan make:model ProductBatch -f
php artisan make:model StockMovement
```

**Batch 3 - Purchasing**
```bash
php artisan make:model Purchase -f
php artisan make:model PurchaseItem
php artisan make:model PurchasePayment
```

**Batch 4 - Sales**
```bash
php artisan make:model CashierShift -f
php artisan make:model CashMovement
php artisan make:model Sale -f
php artisan make:model SaleItem
php artisan make:model SalePayment
php artisan make:model SalePrescription
php artisan make:model PrescriptionItem
```

**Batch 5 - Compounding**
```bash
php artisan make:model CompoundedItem
php artisan make:model CompoundedItemDetail
```

**Batch 6 - Stock Opname & Returns**
```bash
php artisan make:model StockOpname -f
php artisan make:model StockOpnameItem
php artisan make:model PurchaseReturn -f
php artisan make:model PurchaseReturnItem
php artisan make:model SaleReturn -f
php artisan make:model SaleReturnItem
```

**Batch 7 - Settings**
```bash
php artisan make:model Setting
php artisan make:model ActivityLog
```

### 2.2 Manual Testing Phase 2
```bash
# Test dengan tinker
php artisan tinker

# Test model bisa diinstansiasi
> new App\Models\Category;
> new App\Models\Product;
```

**Test Checklist:**
- [ ] Semua model bisa di-instantiate
- [ ] Relationship methods terdefinisi
- [ ] Fillable/Guarded sudah di-set

---

## PHASE 3: Seeders (Data Awal)

### 3.1 Seeders Urutan

```bash
# Buat seeders
php artisan make:seeder UnitSeeder
php artisan make:seeder CategorySeeder
php artisan make:seeder PaymentMethodSeeder
php artisan make:seeder StoreSeeder
php artisan make:seeder UserSeeder
php artisan make:seeder SupplierSeeder
php artisan make:seeder DoctorSeeder
```

### 3.2 Data Default yang Wajib

**Units (Satuan):**
- Tablet, Kapsul, Kaplet
- Botol, Tube, Sachet
- Box, Strip, Blister
- Ampul, Vial
- ml, mg, gram

**Categories:**
- Obat Bebas
- Obat Bebas Terbatas
- Obat Keras
- Narkotika
- Psikotropika
- Alkes
- Kosmetik
- Suplemen
- Obat Tradisional
- Lainnya

**Payment Methods:**
- Cash
- Debit
- Credit Card
- QRIS
- Transfer Bank

### 3.3 Manual Testing Phase 3
```bash
# Run seeder satu-satu untuk debug
php artisan db:seed --class=UnitSeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=PaymentMethodSeeder
php artisan db:seed --class=StoreSeeder
php artisan db:seed --class=UserSeeder

# Atau semua sekaligus
php artisan db:seed
```

**Test dengan Tinker:**
```bash
php artisan tinker

> App\Models\Unit::count();   // Harus > 0
> App\Models\Category::count(); // Harus > 0
> App\Models\User::first();   // Harus ada admin
```

---

## PHASE 4: Filament Installation & Resources

### 4.1 Install Filament
```bash
composer require filament/filament:"^4.0"
php artisan filament:install --panels

# Buat user admin untuk Filament
php artisan make:filament-user
```

### 4.2 Resources - Batch 1 (Master Data)
```bash
php artisan make:filament-resource Category --generate
php artisan make:filament-resource Unit --generate
php artisan make:filament-resource Supplier --generate
php artisan make:filament-resource Doctor --generate
php artisan make:filament-resource Customer --generate
php artisan make:filament-resource PaymentMethod --generate
```

### 4.3 Resources - Batch 2 (Products)
```bash
php artisan make:filament-resource Product --generate
php artisan make:filament-resource ProductBatch --generate
```

### 4.4 Resources - Batch 3 (Transactions)
```bash
php artisan make:filament-resource Purchase --generate
php artisan make:filament-resource Sale --generate
php artisan make:filament-resource CashierShift --generate
```

### 4.5 Resources - Batch 4 (Operations)
```bash
php artisan make:filament-resource StockOpname --generate
php artisan make:filament-resource PurchaseReturn --generate
php artisan make:filament-resource SaleReturn --generate
```

### 4.6 Resources - Batch 5 (System)
```bash
php artisan make:filament-resource User --generate
php artisan make:filament-resource ActivityLog --generate
```

### 4.7 Manual Testing Phase 4
```bash
php artisan serve
```

**Test di Browser:**
- [ ] Akses `/admin` bisa login
- [ ] Sidebar menu muncul semua resources
- [ ] CRUD Category berfungsi
- [ ] CRUD Unit berfungsi
- [ ] CRUD Supplier berfungsi
- [ ] CRUD Product berfungsi

---

## PHASE 5: Services (Business Logic)

### 5.1 Core Services
```bash
mkdir -p app/Services

# Buat service files
touch app/Services/FEFOService.php
touch app/Services/InventoryService.php
touch app/Services/SaleService.php
touch app/Services/PurchaseService.php
touch app/Services/CompoundingService.php
touch app/Services/ReportService.php
touch app/Services/PrintService.php
touch app/Services/PrescriptionService.php
```

### 5.2 Service Prioritas

**FEFOService** - Paling penting karena jadi dasar inventory
- `getAvailableBatches(Product $product, int $qty)`
- `allocateStock(Product $product, int $qty)`
- `deductStock(array $allocations)`
- `updateBatchStatuses()`

**InventoryService**
- `addStock(ProductBatch $batch, int $qty)`
- `reduceStock(ProductBatch $batch, int $qty)`
- `getProductStock(Product $product)`
- `checkLowStock()`
- `checkNearExpired()`

**SaleService**
- `createSale(array $data)`
- `cancelSale(Sale $sale)`
- `generateInvoiceNumber()`
- `calculateTotal(array $items)`

### 5.3 Manual Testing Phase 5
```bash
php artisan tinker

# Test FEFO
> $service = app(App\Services\FEFOService::class);
> $product = App\Models\Product::first();
> $service->getAvailableBatches($product, 10);
```

**Test Scenarios:**
- [ ] FEFO mengambil batch dengan expired date terdekat
- [ ] Stock berkurang setelah sale
- [ ] Stock bertambah setelah purchase received

---

## PHASE 6: Custom Filament Pages

### 6.1 Dashboard Widgets
```bash
php artisan make:filament-widget TodaySalesWidget --stats-overview
php artisan make:filament-widget LowStockWidget
php artisan make:filament-widget NearExpiredWidget
php artisan make:filament-widget TopSellingWidget
php artisan make:filament-widget SalesChartWidget --chart
```

### 6.2 Custom Pages
```bash
php artisan make:filament-page PointOfSale
php artisan make:filament-page StoreSettings
php artisan make:filament-page SalesReport
php artisan make:filament-page StockReport
php artisan make:filament-page ExpiredReport
php artisan make:filament-page NarcoticReport
```

### 6.3 Manual Testing Phase 6
- [ ] Dashboard menampilkan widgets
- [ ] Data di widgets akurat
- [ ] Custom pages bisa diakses

---

## PHASE 7: API Development

### 7.1 Install Sanctum
```bash
php artisan install:api
```

### 7.2 Controllers
```bash
php artisan make:controller Api/AuthController
php artisan make:controller Api/ProductController --api
php artisan make:controller Api/CategoryController --api
php artisan make:controller Api/SaleController --api
php artisan make:controller Api/ShiftController --api
php artisan make:controller Api/CustomerController --api
php artisan make:controller Api/DoctorController --api
php artisan make:controller Api/SyncController
php artisan make:controller Api/ReportController
```

### 7.3 API Resources
```bash
php artisan make:resource ProductResource
php artisan make:resource ProductCollection
php artisan make:resource SaleResource
php artisan make:resource CustomerResource
```

### 7.4 Form Requests
```bash
php artisan make:request StoreSaleRequest
php artisan make:request StorePurchaseRequest
php artisan make:request StoreCustomerRequest
```

### 7.5 Manual Testing Phase 7

**Test dengan curl atau Postman:**

```bash
# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@apotek.com","password":"password"}'

# Get Products (with token)
curl http://localhost:8000/api/v1/products \
  -H "Authorization: Bearer {token}"

# Create Sale
curl -X POST http://localhost:8000/api/v1/sales \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{...}'
```

---

## PHASE 8: Testing (PHPUnit)

### 8.1 Feature Tests
```bash
php artisan make:test Feature/SaleTest
php artisan make:test Feature/PurchaseTest
php artisan make:test Feature/InventoryTest
php artisan make:test Feature/FEFOTest
php artisan make:test Feature/AuthTest
```

### 8.2 Unit Tests
```bash
php artisan make:test Unit/FEFOServiceTest --unit
php artisan make:test Unit/InventoryServiceTest --unit
php artisan make:test Unit/SaleServiceTest --unit
```

### 8.3 Run Tests
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=SaleTest

# Run with coverage
php artisan test --coverage
```

---

## PHASE 9: Commands & Scheduling

### 9.1 Artisan Commands
```bash
php artisan make:command CheckExpiredProducts
php artisan make:command CheckLowStock
php artisan make:command GenerateNarcoticReport
php artisan make:command DatabaseBackup
php artisan make:command SendDailyReport
```

### 9.2 Manual Testing Commands
```bash
# Test individual command
php artisan apotek:check-expired
php artisan apotek:check-low-stock
php artisan apotek:backup-database
```

---

## PHASE 10: Polish & Deployment

### 10.1 Optimization
```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### 10.2 Final Checklist
- [ ] Semua migration stabil
- [ ] Semua seeder berjalan
- [ ] Filament resources CRUD work
- [ ] API endpoints tested
- [ ] Unit tests passing
- [ ] Feature tests passing
- [ ] Commands work correctly
- [ ] Error handling proper
- [ ] Logging configured
- [ ] Backup configured

---

## Quick Commands Reference

```bash
# Fresh install dengan seed
php artisan migrate:fresh --seed

# Clear all cache
php artisan optimize:clear

# Generate IDE helper (optional)
php artisan ide-helper:models

# Check routes
php artisan route:list

# Run queue
php artisan queue:work

# Run scheduler
php artisan schedule:run
```

---

## Development Order Summary

1. **Enums** → Definisi konstanta
2. **Migrations** → Struktur database
3. **Models** → Eloquent & relationships
4. **Seeders** → Data default
5. **Filament** → Admin panel
6. **Services** → Business logic
7. **Widgets & Pages** → Dashboard & reports
8. **API** → Endpoints untuk Flutter
9. **Tests** → Quality assurance
10. **Commands** → Scheduled tasks
11. **Polish** → Optimization & deployment
