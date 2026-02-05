# Analisis Backend POS Apotek
## Laravel 12 + Filament 4

---

## 1. Database Schema (ERD)

### Core Tables

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              MASTER DATA                                         │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ┌──────────────────┐     ┌──────────────────┐     ┌──────────────────┐         │
│  │    categories    │     │      units       │     │   unit_conversions│         │
│  ├──────────────────┤     ├──────────────────┤     ├──────────────────┤         │
│  │ id               │     │ id               │     │ id               │         │
│  │ name             │     │ name             │     │ product_id (FK)  │         │
│  │ type (enum)      │     │ code             │     │ from_unit_id (FK)│         │
│  │ requires_prescription│ │ created_at       │     │ to_unit_id (FK)  │         │
│  │ is_narcotic      │     │ updated_at       │     │ conversion_value │         │
│  │ created_at       │     └──────────────────┘     └──────────────────┘         │
│  │ updated_at       │                                                            │
│  └──────────────────┘                                                            │
│                                                                                  │
│  ┌──────────────────┐     ┌──────────────────┐     ┌──────────────────┐         │
│  │    suppliers     │     │     doctors      │     │    customers     │         │
│  ├──────────────────┤     ├──────────────────┤     ├──────────────────┤         │
│  │ id               │     │ id               │     │ id               │         │
│  │ code             │     │ name             │     │ name             │         │
│  │ name             │     │ sip_number       │     │ phone            │         │
│  │ phone            │     │ specialization   │     │ address          │         │
│  │ address          │     │ phone            │     │ points           │         │
│  │ email            │     │ hospital/clinic  │     │ created_at       │         │
│  │ npwp             │     │ created_at       │     │ updated_at       │         │
│  │ created_at       │     │ updated_at       │     └──────────────────┘         │
│  │ updated_at       │     └──────────────────┘                                   │
│  └──────────────────┘                                                            │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              PRODUCTS & INVENTORY                                │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ┌────────────────────────┐         ┌────────────────────────┐                  │
│  │       products         │         │    product_batches     │                  │
│  ├────────────────────────┤         ├────────────────────────┤                  │
│  │ id                     │────────▶│ id                     │                  │
│  │ code/sku               │         │ product_id (FK)        │                  │
│  │ barcode                │         │ batch_number           │                  │
│  │ name                   │         │ expired_date           │                  │
│  │ generic_name           │         │ purchase_price         │                  │
│  │ category_id (FK)       │         │ selling_price          │                  │
│  │ base_unit_id (FK)      │         │ stock                  │                  │
│  │ purchase_price         │         │ initial_stock          │                  │
│  │ selling_price          │         │ supplier_id (FK)       │                  │
│  │ min_stock              │         │ purchase_id (FK)       │                  │
│  │ max_stock              │         │ status (enum)          │                  │
│  │ rack_location          │         │ created_at             │                  │
│  │ description            │         │ updated_at             │                  │
│  │ requires_prescription  │         └────────────────────────┘                  │
│  │ is_active              │                                                      │
│  │ image                  │         ┌────────────────────────┐                  │
│  │ created_at             │         │    stock_movements     │                  │
│  │ updated_at             │         ├────────────────────────┤                  │
│  │ deleted_at             │         │ id                     │                  │
│  └────────────────────────┘         │ product_batch_id (FK)  │                  │
│                                     │ type (enum)            │                  │
│                                     │ quantity               │                  │
│                                     │ reference_type         │                  │
│                                     │ reference_id           │                  │
│                                     │ notes                  │                  │
│                                     │ user_id (FK)           │                  │
│                                     │ created_at             │                  │
│                                     └────────────────────────┘                  │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              PURCHASING                                          │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ┌────────────────────────┐         ┌────────────────────────┐                  │
│  │      purchases         │         │    purchase_items      │                  │
│  ├────────────────────────┤         ├────────────────────────┤                  │
│  │ id                     │────────▶│ id                     │                  │
│  │ invoice_number         │         │ purchase_id (FK)       │                  │
│  │ supplier_id (FK)       │         │ product_id (FK)        │                  │
│  │ date                   │         │ batch_number           │                  │
│  │ due_date               │         │ expired_date           │                  │
│  │ status (enum)          │         │ quantity               │                  │
│  │ subtotal               │         │ unit_id (FK)           │                  │
│  │ discount               │         │ purchase_price         │                  │
│  │ tax                    │         │ selling_price          │                  │
│  │ total                  │         │ subtotal               │                  │
│  │ paid_amount            │         │ discount               │                  │
│  │ notes                  │         │ total                  │                  │
│  │ user_id (FK)           │         │ created_at             │                  │
│  │ created_at             │         └────────────────────────┘                  │
│  │ updated_at             │                                                      │
│  └────────────────────────┘         ┌────────────────────────┐                  │
│                                     │  purchase_payments     │                  │
│                                     ├────────────────────────┤                  │
│                                     │ id                     │                  │
│                                     │ purchase_id (FK)       │                  │
│                                     │ amount                 │                  │
│                                     │ payment_method         │                  │
│                                     │ payment_date           │                  │
│                                     │ notes                  │                  │
│                                     │ user_id (FK)           │                  │
│                                     │ created_at             │                  │
│                                     └────────────────────────┘                  │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              SALES / POS                                         │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ┌────────────────────────┐         ┌────────────────────────┐                  │
│  │        sales           │         │      sale_items        │                  │
│  ├────────────────────────┤         ├────────────────────────┤                  │
│  │ id                     │────────▶│ id                     │                  │
│  │ invoice_number         │         │ sale_id (FK)           │                  │
│  │ customer_id (FK) null  │         │ product_id (FK)        │                  │
│  │ doctor_id (FK) null    │         │ product_batch_id (FK)  │                  │
│  │ prescription_number    │         │ quantity               │                  │
│  │ is_prescription        │         │ unit_id (FK)           │                  │
│  │ patient_name           │         │ price                  │                  │
│  │ patient_address        │         │ discount               │                  │
│  │ date                   │         │ subtotal               │                  │
│  │ subtotal               │         │ is_prescription_item   │                  │
│  │ discount               │         │ notes                  │                  │
│  │ tax                    │         │ created_at             │                  │
│  │ total                  │         └────────────────────────┘                  │
│  │ paid_amount            │                                                      │
│  │ change_amount          │         ┌────────────────────────┐                  │
│  │ payment_method         │         │    sale_payments       │                  │
│  │ status (enum)          │         ├────────────────────────┤                  │
│  │ notes                  │         │ id                     │                  │
│  │ user_id (FK)           │         │ sale_id (FK)           │                  │
│  │ shift_id (FK)          │         │ payment_method_id (FK) │                  │
│  │ created_at             │         │ amount                 │                  │
│  │ updated_at             │         │ reference_number       │                  │
│  │ deleted_at             │         │ created_at             │                  │
│  └────────────────────────┘         └────────────────────────┘                  │
│                                                                                  │
│  ┌────────────────────────┐         ┌────────────────────────┐                  │
│  │    sale_prescriptions  │         │   prescription_items   │                  │
│  ├────────────────────────┤         ├────────────────────────┤                  │
│  │ id                     │────────▶│ id                     │                  │
│  │ sale_id (FK)           │         │ sale_prescription_id   │                  │
│  │ prescription_number    │         │ product_id (FK)        │                  │
│  │ doctor_id (FK)         │         │ quantity               │                  │
│  │ patient_name           │         │ dosage                 │                  │
│  │ patient_age            │         │ frequency              │                  │
│  │ patient_address        │         │ duration               │                  │
│  │ diagnosis              │         │ instructions           │                  │
│  │ date                   │         │ is_compounded          │                  │
│  │ is_copy                │         │ created_at             │                  │
│  │ copy_number            │         └────────────────────────┘                  │
│  │ created_at             │                                                      │
│  └────────────────────────┘                                                      │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              RACIKAN (COMPOUNDING)                               │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ┌────────────────────────┐         ┌────────────────────────┐                  │
│  │    compounded_items    │         │ compounded_item_details│                  │
│  ├────────────────────────┤         ├────────────────────────┤                  │
│  │ id                     │────────▶│ id                     │                  │
│  │ sale_item_id (FK)      │         │ compounded_item_id (FK)│                  │
│  │ name                   │         │ product_id (FK)        │                  │
│  │ type (puyer/kapsul/dll)│         │ product_batch_id (FK)  │                  │
│  │ quantity               │         │ quantity               │                  │
│  │ price                  │         │ unit_id (FK)           │                  │
│  │ instructions           │         │ price                  │                  │
│  │ created_at             │         │ created_at             │                  │
│  └────────────────────────┘         └────────────────────────┘                  │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              CASHIER MANAGEMENT                                  │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ┌────────────────────────┐         ┌────────────────────────┐                  │
│  │    cashier_shifts      │         │   cash_movements       │                  │
│  ├────────────────────────┤         ├────────────────────────┤                  │
│  │ id                     │────────▶│ id                     │                  │
│  │ user_id (FK)           │         │ shift_id (FK)          │                  │
│  │ opening_time           │         │ type (in/out)          │                  │
│  │ closing_time           │         │ amount                 │                  │
│  │ opening_cash           │         │ reason                 │                  │
│  │ expected_cash          │         │ notes                  │                  │
│  │ actual_cash            │         │ user_id (FK)           │                  │
│  │ difference             │         │ created_at             │                  │
│  │ status (open/closed)   │         └────────────────────────┘                  │
│  │ notes                  │                                                      │
│  │ created_at             │         ┌────────────────────────┐                  │
│  │ updated_at             │         │   payment_methods      │                  │
│  └────────────────────────┘         ├────────────────────────┤                  │
│                                     │ id                     │                  │
│                                     │ name                   │                  │
│                                     │ code                   │                  │
│                                     │ is_cash                │                  │
│                                     │ is_active              │                  │
│                                     │ account_number         │                  │
│                                     │ created_at             │                  │
│                                     └────────────────────────┘                  │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              STOCK OPNAME                                        │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ┌────────────────────────┐         ┌────────────────────────┐                  │
│  │    stock_opnames       │         │  stock_opname_items    │                  │
│  ├────────────────────────┤         ├────────────────────────┤                  │
│  │ id                     │────────▶│ id                     │                  │
│  │ code                   │         │ stock_opname_id (FK)   │                  │
│  │ date                   │         │ product_id (FK)        │                  │
│  │ status (enum)          │         │ product_batch_id (FK)  │                  │
│  │ notes                  │         │ system_stock           │                  │
│  │ user_id (FK)           │         │ physical_stock         │                  │
│  │ approved_by (FK)       │         │ difference             │                  │
│  │ approved_at            │         │ notes                  │                  │
│  │ created_at             │         │ created_at             │                  │
│  │ updated_at             │         └────────────────────────┘                  │
│  └────────────────────────┘                                                      │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              RETURNS                                             │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ┌────────────────────────┐         ┌────────────────────────┐                  │
│  │   purchase_returns     │         │ purchase_return_items  │                  │
│  ├────────────────────────┤         ├────────────────────────┤                  │
│  │ id                     │────────▶│ id                     │                  │
│  │ code                   │         │ purchase_return_id (FK)│                  │
│  │ purchase_id (FK)       │         │ product_id (FK)        │                  │
│  │ supplier_id (FK)       │         │ product_batch_id (FK)  │                  │
│  │ date                   │         │ quantity               │                  │
│  │ reason                 │         │ reason                 │                  │
│  │ total                  │         │ price                  │                  │
│  │ status (enum)          │         │ subtotal               │                  │
│  │ user_id (FK)           │         │ created_at             │                  │
│  │ created_at             │         └────────────────────────┘                  │
│  └────────────────────────┘                                                      │
│                                                                                  │
│  ┌────────────────────────┐         ┌────────────────────────┐                  │
│  │    sale_returns        │         │   sale_return_items    │                  │
│  ├────────────────────────┤         ├────────────────────────┤                  │
│  │ id                     │────────▶│ id                     │                  │
│  │ code                   │         │ sale_return_id (FK)    │                  │
│  │ sale_id (FK)           │         │ sale_item_id (FK)      │                  │
│  │ customer_id (FK)       │         │ product_id (FK)        │                  │
│  │ date                   │         │ product_batch_id (FK)  │                  │
│  │ reason                 │         │ quantity               │                  │
│  │ total                  │         │ reason                 │                  │
│  │ refund_method          │         │ price                  │                  │
│  │ status (enum)          │         │ subtotal               │                  │
│  │ user_id (FK)           │         │ created_at             │                  │
│  │ created_at             │         └────────────────────────┘                  │
│  └────────────────────────┘                                                      │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              SETTINGS & CONFIG                                   │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ┌────────────────────────┐         ┌────────────────────────┐                  │
│  │       stores           │         │       settings         │                  │
│  ├────────────────────────┤         ├────────────────────────┤                  │
│  │ id                     │         │ id                     │                  │
│  │ name                   │         │ store_id (FK)          │                  │
│  │ code                   │         │ key                    │                  │
│  │ address                │         │ value                  │                  │
│  │ phone                  │         │ group                  │                  │
│  │ email                  │         │ created_at             │                  │
│  │ sia_number             │         │ updated_at             │                  │
│  │ sipa_number            │         └────────────────────────┘                  │
│  │ pharmacist_name        │                                                      │
│  │ pharmacist_sipa        │         ┌────────────────────────┐                  │
│  │ logo                   │         │    activity_logs       │                  │
│  │ receipt_footer         │         ├────────────────────────┤                  │
│  │ created_at             │         │ id                     │                  │
│  │ updated_at             │         │ user_id (FK)           │                  │
│  └────────────────────────┘         │ action                 │                  │
│                                     │ model_type             │                  │
│  ┌────────────────────────┐         │ model_id               │                  │
│  │        users           │         │ old_values             │                  │
│  ├────────────────────────┤         │ new_values             │                  │
│  │ id                     │         │ ip_address             │                  │
│  │ name                   │         │ user_agent             │                  │
│  │ email                  │         │ created_at             │                  │
│  │ password               │         └────────────────────────┘                  │
│  │ role (enum)            │                                                      │
│  │ phone                  │                                                      │
│  │ is_active              │                                                      │
│  │ store_id (FK)          │                                                      │
│  │ created_at             │                                                      │
│  │ updated_at             │                                                      │
│  └────────────────────────┘                                                      │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Enum Definitions

```php
<?php

namespace App\Enums;

enum CategoryType: string
{
    case OBAT_BEBAS = 'obat_bebas';
    case OBAT_BEBAS_TERBATAS = 'obat_bebas_terbatas';
    case OBAT_KERAS = 'obat_keras';
    case NARKOTIKA = 'narkotika';
    case PSIKOTROPIKA = 'psikotropika';
    case ALKES = 'alkes';
    case KOSMETIK = 'kosmetik';
    case SUPLEMEN = 'suplemen';
    case OBAT_TRADISIONAL = 'obat_tradisional';
    case LAINNYA = 'lainnya';
}

enum BatchStatus: string
{
    case ACTIVE = 'active';
    case NEAR_EXPIRED = 'near_expired';  // < 90 hari
    case EXPIRED = 'expired';
    case RETURNED = 'returned';
    case DAMAGED = 'damaged';
}

enum PurchaseStatus: string
{
    case DRAFT = 'draft';
    case ORDERED = 'ordered';
    case PARTIAL = 'partial';
    case RECEIVED = 'received';
    case CANCELLED = 'cancelled';
}

enum SaleStatus: string
{
    case COMPLETED = 'completed';
    case PENDING = 'pending';
    case CANCELLED = 'cancelled';
    case RETURNED = 'returned';
}

enum StockMovementType: string
{
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case RETURN_SUPPLIER = 'return_supplier';
    case RETURN_CUSTOMER = 'return_customer';
    case ADJUSTMENT_IN = 'adjustment_in';
    case ADJUSTMENT_OUT = 'adjustment_out';
    case DAMAGED = 'damaged';
    case EXPIRED = 'expired';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
}

enum UserRole: string
{
    case OWNER = 'owner';
    case PHARMACIST = 'pharmacist';      // Apoteker
    case ASSISTANT = 'assistant';         // Asisten Apoteker
    case CASHIER = 'cashier';
    case INVENTORY = 'inventory';         // Staff Gudang
}

enum CompoundType: string
{
    case PUYER = 'puyer';
    case KAPSUL = 'kapsul';
    case SIRUP = 'sirup';
    case SALEP = 'salep';
    case CREAM = 'cream';
}

enum ShiftStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
}

enum StockOpnameStatus: string
{
    case DRAFT = 'draft';
    case IN_PROGRESS = 'in_progress';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case CANCELLED = 'cancelled';
}
```

---

## 3. Laravel Project Structure

```
apotek-pos/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── CheckExpiredProducts.php
│   │       ├── CheckLowStock.php
│   │       ├── GenerateNarcoticReport.php
│   │       └── DatabaseBackup.php
│   │
│   ├── Enums/
│   │   ├── CategoryType.php
│   │   ├── BatchStatus.php
│   │   ├── PurchaseStatus.php
│   │   ├── SaleStatus.php
│   │   ├── StockMovementType.php
│   │   ├── UserRole.php
│   │   ├── CompoundType.php
│   │   ├── ShiftStatus.php
│   │   └── StockOpnameStatus.php
│   │
│   ├── Events/
│   │   ├── ProductSold.php
│   │   ├── StockLow.php
│   │   ├── ProductNearExpired.php
│   │   └── SaleCompleted.php
│   │
│   ├── Filament/
│   │   ├── Pages/
│   │   │   ├── Dashboard.php
│   │   │   ├── PointOfSale.php
│   │   │   ├── Reports/
│   │   │   │   ├── SalesReport.php
│   │   │   │   ├── PurchaseReport.php
│   │   │   │   ├── StockReport.php
│   │   │   │   ├── ExpiredReport.php
│   │   │   │   ├── NarcoticReport.php
│   │   │   │   ├── PrescriptionReport.php
│   │   │   │   └── ProfitLossReport.php
│   │   │   └── Settings/
│   │   │       ├── StoreSettings.php
│   │   │       ├── PrinterSettings.php
│   │   │       └── BackupSettings.php
│   │   │
│   │   ├── Resources/
│   │   │   ├── CategoryResource.php
│   │   │   ├── UnitResource.php
│   │   │   ├── SupplierResource.php
│   │   │   ├── DoctorResource.php
│   │   │   ├── CustomerResource.php
│   │   │   ├── ProductResource.php
│   │   │   ├── ProductBatchResource.php
│   │   │   ├── PurchaseResource.php
│   │   │   ├── SaleResource.php
│   │   │   ├── PrescriptionResource.php
│   │   │   ├── StockOpnameResource.php
│   │   │   ├── PurchaseReturnResource.php
│   │   │   ├── SaleReturnResource.php
│   │   │   ├── CashierShiftResource.php
│   │   │   ├── UserResource.php
│   │   │   └── ActivityLogResource.php
│   │   │
│   │   └── Widgets/
│   │       ├── TodaySalesWidget.php
│   │       ├── LowStockWidget.php
│   │       ├── NearExpiredWidget.php
│   │       ├── TopSellingWidget.php
│   │       ├── SalesChartWidget.php
│   │       ├── RevenueChartWidget.php
│   │       └── AlertsWidget.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── ProductController.php
│   │   │       ├── CategoryController.php
│   │   │       ├── SaleController.php
│   │   │       ├── CustomerController.php
│   │   │       ├── DoctorController.php
│   │   │       ├── ShiftController.php
│   │   │       ├── ReportController.php
│   │   │       └── SyncController.php
│   │   │
│   │   ├── Requests/
│   │   │   ├── StoreSaleRequest.php
│   │   │   ├── StorePurchaseRequest.php
│   │   │   └── ...
│   │   │
│   │   └── Resources/
│   │       ├── ProductResource.php
│   │       ├── SaleResource.php
│   │       └── ...
│   │
│   ├── Jobs/
│   │   ├── ProcessExpiredProducts.php
│   │   ├── GenerateReport.php
│   │   ├── SendLowStockNotification.php
│   │   └── SyncOfflineData.php
│   │
│   ├── Listeners/
│   │   ├── UpdateStockOnSale.php
│   │   ├── CreateStockMovement.php
│   │   └── SendSaleReceipt.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Store.php
│   │   ├── Category.php
│   │   ├── Unit.php
│   │   ├── UnitConversion.php
│   │   ├── Supplier.php
│   │   ├── Doctor.php
│   │   ├── Customer.php
│   │   ├── Product.php
│   │   ├── ProductBatch.php
│   │   ├── StockMovement.php
│   │   ├── Purchase.php
│   │   ├── PurchaseItem.php
│   │   ├── PurchasePayment.php
│   │   ├── Sale.php
│   │   ├── SaleItem.php
│   │   ├── SalePayment.php
│   │   ├── SalePrescription.php
│   │   ├── PrescriptionItem.php
│   │   ├── CompoundedItem.php
│   │   ├── CompoundedItemDetail.php
│   │   ├── CashierShift.php
│   │   ├── CashMovement.php
│   │   ├── PaymentMethod.php
│   │   ├── StockOpname.php
│   │   ├── StockOpnameItem.php
│   │   ├── PurchaseReturn.php
│   │   ├── PurchaseReturnItem.php
│   │   ├── SaleReturn.php
│   │   ├── SaleReturnItem.php
│   │   ├── Setting.php
│   │   └── ActivityLog.php
│   │
│   ├── Notifications/
│   │   ├── LowStockNotification.php
│   │   ├── ExpiredProductNotification.php
│   │   └── DailySalesReportNotification.php
│   │
│   ├── Observers/
│   │   ├── SaleObserver.php
│   │   ├── PurchaseObserver.php
│   │   ├── ProductBatchObserver.php
│   │   └── StockMovementObserver.php
│   │
│   ├── Policies/
│   │   ├── ProductPolicy.php
│   │   ├── SalePolicy.php
│   │   ├── PurchasePolicy.php
│   │   └── ...
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   └── FilamentServiceProvider.php
│   │
│   ├── Services/
│   │   ├── InventoryService.php
│   │   ├── SaleService.php
│   │   ├── PurchaseService.php
│   │   ├── ReportService.php
│   │   ├── FEFOService.php
│   │   ├── PrescriptionService.php
│   │   ├── CompoundingService.php
│   │   └── PrintService.php
│   │
│   └── Traits/
│       ├── HasStore.php
│       ├── HasActivityLog.php
│       └── GeneratesInvoiceNumber.php
│
├── config/
│   ├── apotek.php              # Custom config
│   └── ...
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│       ├── CategorySeeder.php
│       ├── UnitSeeder.php
│       ├── PaymentMethodSeeder.php
│       ├── DefaultSettingsSeeder.php
│       └── DemoDataSeeder.php
│
├── resources/
│   └── views/
│       ├── filament/
│       │   └── pages/
│       │       └── point-of-sale.blade.php
│       ├── pdf/
│       │   ├── receipt.blade.php
│       │   ├── prescription-label.blade.php
│       │   ├── purchase-order.blade.php
│       │   └── reports/
│       │       ├── daily-sales.blade.php
│       │       ├── narcotic-report.blade.php
│       │       └── stock-report.blade.php
│       └── emails/
│           ├── daily-report.blade.php
│           └── low-stock-alert.blade.php
│
├── routes/
│   ├── api.php
│   └── web.php
│
└── tests/
    ├── Feature/
    │   ├── SaleTest.php
    │   ├── PurchaseTest.php
    │   ├── InventoryTest.php
    │   └── FEFOTest.php
    └── Unit/
        ├── FEFOServiceTest.php
        └── CompoundingServiceTest.php
```

---

## 4. Filament Dashboard Menu Structure

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           FILAMENT SIDEBAR MENU                                  │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  🏠 DASHBOARD                                                                    │
│  └── Overview (widgets: sales, alerts, charts)                                  │
│                                                                                  │
│  ─────────────────────────────────────────────────────────────────────────────  │
│                                                                                  │
│  💊 KASIR / POS                                                                  │
│  ├── 🛒 Point of Sale          [Custom Page - Full POS Interface]               │
│  ├── 📋 Riwayat Penjualan      [SaleResource - List/View/Print]                 │
│  ├── 📝 Resep                  [PrescriptionResource]                           │
│  └── 💰 Shift Kasir            [CashierShiftResource]                           │
│                                                                                  │
│  ─────────────────────────────────────────────────────────────────────────────  │
│                                                                                  │
│  📦 INVENTORY                                                                    │
│  ├── 💊 Produk/Obat            [ProductResource]                                │
│  │   └── Batch & Expired       [ProductBatchResource - nested]                  │
│  ├── 📂 Kategori               [CategoryResource]                               │
│  ├── 📏 Satuan                 [UnitResource]                                   │
│  ├── 📊 Kartu Stok             [StockMovement - read only]                      │
│  ├── 📝 Stock Opname           [StockOpnameResource]                            │
│  └── ⚠️ Stok & Expired Alert   [Custom Page]                                    │
│                                                                                  │
│  ─────────────────────────────────────────────────────────────────────────────  │
│                                                                                  │
│  🛍️ PEMBELIAN                                                                    │
│  ├── 📝 Purchase Order         [PurchaseResource]                               │
│  ├── 📥 Penerimaan Barang      [PurchaseResource - receive action]              │
│  ├── ↩️ Retur ke Supplier      [PurchaseReturnResource]                         │
│  └── 🏭 Supplier               [SupplierResource]                               │
│                                                                                  │
│  ─────────────────────────────────────────────────────────────────────────────  │
│                                                                                  │
│  👥 MASTER DATA                                                                  │
│  ├── 👨‍⚕️ Dokter                 [DoctorResource]                                 │
│  ├── 👤 Pelanggan              [CustomerResource]                               │
│  └── 💳 Metode Pembayaran      [PaymentMethodResource]                          │
│                                                                                  │
│  ─────────────────────────────────────────────────────────────────────────────  │
│                                                                                  │
│  📊 LAPORAN                                                                      │
│  ├── 💰 Laporan Penjualan      [Custom Page]                                    │
│  ├── 🛒 Laporan Pembelian      [Custom Page]                                    │
│  ├── 📦 Laporan Stok           [Custom Page]                                    │
│  ├── ⚠️ Laporan Expired        [Custom Page]                                    │
│  ├── 💊 Laporan Obat Keras     [Custom Page - MANDATORY]                        │
│  ├── 🚨 Laporan Narkotika      [Custom Page - MANDATORY]                        │
│  ├── 📝 Laporan Resep          [Custom Page]                                    │
│  └── 📈 Laba Rugi              [Custom Page]                                    │
│                                                                                  │
│  ─────────────────────────────────────────────────────────────────────────────  │
│                                                                                  │
│  ⚙️ PENGATURAN                                                                   │
│  ├── 🏪 Profil Apotek          [Custom Page]                                    │
│  ├── 👥 Manajemen User         [UserResource]                                   │
│  ├── 🖨️ Pengaturan Printer     [Custom Page]                                    │
│  ├── 💾 Backup & Restore       [Custom Page]                                    │
│  └── 📜 Activity Log           [ActivityLogResource - read only]                │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 5. Role-Based Access Control (RBAC)

```php
<?php

// config/apotek.php

return [
    'roles' => [
        'owner' => [
            'label' => 'Pemilik',
            'permissions' => ['*'], // All access
        ],
        
        'pharmacist' => [
            'label' => 'Apoteker',
            'permissions' => [
                'dashboard.view',
                'pos.*',
                'sale.*',
                'prescription.*',
                'product.view',
                'product.create',
                'product.edit',
                'batch.*',
                'stock_movement.view',
                'stock_opname.*',
                'purchase.view',
                'purchase.create',
                'purchase.receive',
                'supplier.view',
                'doctor.*',
                'customer.*',
                'report.sales',
                'report.stock',
                'report.expired',
                'report.narcotic',
                'report.prescription',
                'shift.*',
            ],
        ],
        
        'assistant' => [
            'label' => 'Asisten Apoteker',
            'permissions' => [
                'dashboard.view',
                'pos.*',
                'sale.view',
                'sale.create',
                'prescription.view',
                'prescription.create',
                'product.view',
                'batch.view',
                'stock_movement.view',
                'doctor.view',
                'customer.view',
                'customer.create',
                'shift.*',
            ],
        ],
        
        'cashier' => [
            'label' => 'Kasir',
            'permissions' => [
                'pos.*',
                'sale.view',
                'sale.create',
                'product.view',
                'batch.view',
                'customer.view',
                'customer.create',
                'shift.own',
            ],
        ],
        
        'inventory' => [
            'label' => 'Staff Gudang',
            'permissions' => [
                'dashboard.view',
                'product.*',
                'batch.*',
                'stock_movement.*',
                'stock_opname.*',
                'purchase.*',
                'purchase_return.*',
                'supplier.*',
                'report.stock',
                'report.expired',
            ],
        ],
    ],
];
```

---

## 6. Key Services Implementation

### 6.1 FEFO Service (First Expired First Out)

```php
<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Enums\BatchStatus;
use Illuminate\Support\Collection;

class FEFOService
{
    /**
     * Get available batches for a product using FEFO method
     * Prioritizes batches that expire soonest
     */
    public function getAvailableBatches(Product $product, int $requiredQuantity): Collection
    {
        return ProductBatch::where('product_id', $product->id)
            ->where('stock', '>', 0)
            ->where('status', BatchStatus::ACTIVE)
            ->where('expired_date', '>', now())
            ->orderBy('expired_date', 'asc') // FEFO: earliest expiry first
            ->get();
    }

    /**
     * Allocate stock from multiple batches using FEFO
     * Returns array of batch allocations
     */
    public function allocateStock(Product $product, int $requiredQuantity): array
    {
        $batches = $this->getAvailableBatches($product, $requiredQuantity);
        $allocations = [];
        $remaining = $requiredQuantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $allocateQty = min($batch->stock, $remaining);
            $allocations[] = [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'expired_date' => $batch->expired_date,
                'quantity' => $allocateQty,
                'price' => $batch->selling_price,
            ];
            $remaining -= $allocateQty;
        }

        if ($remaining > 0) {
            throw new \Exception("Insufficient stock. Short by {$remaining} units.");
        }

        return $allocations;
    }

    /**
     * Deduct stock from batches after sale
     */
    public function deductStock(array $allocations): void
    {
        foreach ($allocations as $allocation) {
            $batch = ProductBatch::find($allocation['batch_id']);
            $batch->decrement('stock', $allocation['quantity']);
            
            // Create stock movement record
            StockMovement::create([
                'product_batch_id' => $batch->id,
                'type' => StockMovementType::SALE,
                'quantity' => -$allocation['quantity'],
                'reference_type' => 'sale',
                'reference_id' => $allocation['sale_id'] ?? null,
            ]);
        }
    }

    /**
     * Check and update batch statuses based on expiry
     */
    public function updateBatchStatuses(): int
    {
        $updated = 0;

        // Mark as near expired (< 90 days)
        $updated += ProductBatch::where('status', BatchStatus::ACTIVE)
            ->where('expired_date', '<=', now()->addDays(90))
            ->where('expired_date', '>', now())
            ->update(['status' => BatchStatus::NEAR_EXPIRED]);

        // Mark as expired
        $updated += ProductBatch::whereIn('status', [BatchStatus::ACTIVE, BatchStatus::NEAR_EXPIRED])
            ->where('expired_date', '<=', now())
            ->update(['status' => BatchStatus::EXPIRED]);

        return $updated;
    }
}
```

### 6.2 Sale Service

```php
<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\CashierShift;
use App\Enums\SaleStatus;
use Illuminate\Support\Facades\DB;

class SaleService
{
    protected FEFOService $fefoService;
    protected InventoryService $inventoryService;

    public function __construct(FEFOService $fefoService, InventoryService $inventoryService)
    {
        $this->fefoService = $fefoService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new sale transaction
     */
    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // Validate shift is open
            $shift = CashierShift::where('user_id', auth()->id())
                ->where('status', 'open')
                ->firstOrFail();

            // Create sale header
            $sale = Sale::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $data['customer_id'] ?? null,
                'doctor_id' => $data['doctor_id'] ?? null,
                'is_prescription' => $data['is_prescription'] ?? false,
                'patient_name' => $data['patient_name'] ?? null,
                'date' => now(),
                'subtotal' => 0,
                'discount' => $data['discount'] ?? 0,
                'tax' => 0,
                'total' => 0,
                'paid_amount' => $data['paid_amount'],
                'payment_method' => $data['payment_method'],
                'status' => SaleStatus::COMPLETED,
                'user_id' => auth()->id(),
                'shift_id' => $shift->id,
            ]);

            $subtotal = 0;

            // Process each item
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Use FEFO to allocate batches
                $allocations = $this->fefoService->allocateStock($product, $item['quantity']);
                
                foreach ($allocations as $allocation) {
                    $itemSubtotal = $allocation['quantity'] * $allocation['price'];
                    $itemDiscount = $item['discount'] ?? 0;
                    
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'product_batch_id' => $allocation['batch_id'],
                        'quantity' => $allocation['quantity'],
                        'unit_id' => $item['unit_id'],
                        'price' => $allocation['price'],
                        'discount' => $itemDiscount,
                        'subtotal' => $itemSubtotal - $itemDiscount,
                        'is_prescription_item' => $item['is_prescription'] ?? false,
                    ]);
                    
                    $subtotal += ($itemSubtotal - $itemDiscount);
                    
                    // Deduct stock
                    $this->fefoService->deductStock([
                        array_merge($allocation, ['sale_id' => $sale->id])
                    ]);
                }
            }

            // Update sale totals
            $sale->update([
                'subtotal' => $subtotal,
                'total' => $subtotal - $sale->discount,
                'change_amount' => $data['paid_amount'] - ($subtotal - $sale->discount),
            ]);

            // Process payments
            foreach ($data['payments'] as $payment) {
                $sale->payments()->create([
                    'payment_method_id' => $payment['method_id'],
                    'amount' => $payment['amount'],
                    'reference_number' => $payment['reference'] ?? null,
                ]);
            }

            return $sale->fresh(['items', 'payments', 'customer']);
        });
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastSale = Sale::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastSale 
            ? (int) substr($lastSale->invoice_number, -4) + 1 
            : 1;
        
        return sprintf('%s%s%04d', $prefix, $date, $sequence);
    }
}
```

### 6.3 Compounding Service (Racikan)

```php
<?php

namespace App\Services;

use App\Models\SaleItem;
use App\Models\CompoundedItem;
use App\Models\CompoundedItemDetail;
use App\Enums\CompoundType;
use Illuminate\Support\Facades\DB;

class CompoundingService
{
    protected FEFOService $fefoService;

    public function __construct(FEFOService $fefoService)
    {
        $this->fefoService = $fefoService;
    }

    /**
     * Create a compounded medicine (racikan)
     */
    public function createCompound(array $data): CompoundedItem
    {
        return DB::transaction(function () use ($data) {
            // Create sale item for the compound
            $saleItem = SaleItem::create([
                'sale_id' => $data['sale_id'],
                'product_id' => null, // No single product
                'quantity' => $data['quantity'],
                'price' => $this->calculateCompoundPrice($data['ingredients']),
                'subtotal' => $data['quantity'] * $this->calculateCompoundPrice($data['ingredients']),
                'is_prescription_item' => true,
            ]);

            // Create compound record
            $compound = CompoundedItem::create([
                'sale_item_id' => $saleItem->id,
                'name' => $data['name'] ?? 'Racikan',
                'type' => CompoundType::from($data['type']),
                'quantity' => $data['quantity'],
                'price' => $saleItem->price,
                'instructions' => $data['instructions'] ?? null,
            ]);

            // Add ingredients and deduct stock
            foreach ($data['ingredients'] as $ingredient) {
                $allocations = $this->fefoService->allocateStock(
                    Product::find($ingredient['product_id']),
                    $ingredient['quantity'] * $data['quantity']
                );

                foreach ($allocations as $allocation) {
                    CompoundedItemDetail::create([
                        'compounded_item_id' => $compound->id,
                        'product_id' => $ingredient['product_id'],
                        'product_batch_id' => $allocation['batch_id'],
                        'quantity' => $allocation['quantity'],
                        'unit_id' => $ingredient['unit_id'],
                        'price' => $allocation['price'],
                    ]);

                    // Deduct stock
                    $this->fefoService->deductStock([$allocation]);
                }
            }

            return $compound->fresh(['details']);
        });
    }

    /**
     * Calculate total price of compound based on ingredients
     */
    protected function calculateCompoundPrice(array $ingredients): float
    {
        $total = 0;
        foreach ($ingredients as $ingredient) {
            $product = Product::find($ingredient['product_id']);
            $total += $product->selling_price * $ingredient['quantity'];
        }
        
        // Add compounding fee (configurable)
        $compoundingFee = config('apotek.compounding_fee', 5000);
        
        return $total + $compoundingFee;
    }
}
```

---

## 7. API Endpoints for Flutter

```php
<?php

// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\*;

Route::prefix('v1')->group(function () {
    
    // Authentication
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
    
    Route::middleware('auth:sanctum')->group(function () {
        
        // ═══════════════════════════════════════════════════════════════════
        // MASTER DATA
        // ═══════════════════════════════════════════════════════════════════
        
        // Products
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        Route::get('/products/barcode/{barcode}', [ProductController::class, 'findByBarcode']);
        Route::get('/products/search', [ProductController::class, 'search']);
        Route::get('/products/{id}/batches', [ProductController::class, 'batches']);
        Route::get('/products/{id}/stock', [ProductController::class, 'stockInfo']);
        
        // Categories
        Route::get('/categories', [CategoryController::class, 'index']);
        
        // Units
        Route::get('/units', [UnitController::class, 'index']);
        
        // Doctors
        Route::apiResource('/doctors', DoctorController::class);
        Route::get('/doctors/search', [DoctorController::class, 'search']);
        
        // Customers
        Route::apiResource('/customers', CustomerController::class);
        Route::get('/customers/search', [CustomerController::class, 'search']);
        Route::get('/customers/{id}/history', [CustomerController::class, 'purchaseHistory']);
        
        // Payment Methods
        Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
        
        // ═══════════════════════════════════════════════════════════════════
        // POS / SALES
        // ═══════════════════════════════════════════════════════════════════
        
        // Sales
        Route::get('/sales', [SaleController::class, 'index']);
        Route::post('/sales', [SaleController::class, 'store']);
        Route::get('/sales/{id}', [SaleController::class, 'show']);
        Route::post('/sales/{id}/cancel', [SaleController::class, 'cancel']);
        Route::get('/sales/{id}/receipt', [SaleController::class, 'receipt']);
        Route::post('/sales/{id}/print', [SaleController::class, 'print']);
        
        // Prescriptions
        Route::post('/prescriptions', [PrescriptionController::class, 'store']);
        Route::get('/prescriptions/{id}', [PrescriptionController::class, 'show']);
        Route::get('/prescriptions/{id}/copy', [PrescriptionController::class, 'createCopy']);
        
        // Compounding (Racikan)
        Route::post('/compounds', [CompoundController::class, 'store']);
        Route::get('/compounds/calculate', [CompoundController::class, 'calculatePrice']);
        
        // ═══════════════════════════════════════════════════════════════════
        // CASHIER SHIFT
        // ═══════════════════════════════════════════════════════════════════
        
        Route::get('/shifts/current', [ShiftController::class, 'current']);
        Route::post('/shifts/open', [ShiftController::class, 'open']);
        Route::post('/shifts/close', [ShiftController::class, 'close']);
        Route::post('/shifts/cash-in', [ShiftController::class, 'cashIn']);
        Route::post('/shifts/cash-out', [ShiftController::class, 'cashOut']);
        Route::get('/shifts/{id}/summary', [ShiftController::class, 'summary']);
        
        // ═══════════════════════════════════════════════════════════════════
        // INVENTORY (Limited for POS app)
        // ═══════════════════════════════════════════════════════════════════
        
        Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock']);
        Route::get('/inventory/near-expired', [InventoryController::class, 'nearExpired']);
        Route::get('/inventory/expired', [InventoryController::class, 'expired']);
        
        // ═══════════════════════════════════════════════════════════════════
        // REPORTS (Basic for POS app)
        // ═══════════════════════════════════════════════════════════════════
        
        Route::get('/reports/daily-sales', [ReportController::class, 'dailySales']);
        Route::get('/reports/shift-summary', [ReportController::class, 'shiftSummary']);
        
        // ═══════════════════════════════════════════════════════════════════
        // SYNC (For offline support)
        // ═══════════════════════════════════════════════════════════════════
        
        Route::get('/sync/products', [SyncController::class, 'products']);
        Route::get('/sync/categories', [SyncController::class, 'categories']);
        Route::get('/sync/doctors', [SyncController::class, 'doctors']);
        Route::post('/sync/sales', [SyncController::class, 'syncSales']);
        Route::get('/sync/status', [SyncController::class, 'status']);
    });
});
```

---

## 8. Filament Dashboard Widgets

### 8.1 Today Sales Widget

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodaySalesWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $today = Sale::whereDate('created_at', today());
        $yesterday = Sale::whereDate('created_at', today()->subDay());
        
        $todaySales = $today->sum('total');
        $yesterdaySales = $yesterday->sum('total');
        $growth = $yesterdaySales > 0 
            ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 
            : 0;
        
        return [
            Stat::make('Penjualan Hari Ini', 'Rp ' . number_format($todaySales, 0, ',', '.'))
                ->description($growth >= 0 ? "+{$growth}% dari kemarin" : "{$growth}% dari kemarin")
                ->descriptionIcon($growth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growth >= 0 ? 'success' : 'danger')
                ->chart($this->getWeeklyChart()),
            
            Stat::make('Transaksi', $today->count())
                ->description('Total transaksi hari ini')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
            
            Stat::make('Rata-rata Transaksi', 'Rp ' . number_format($today->avg('total') ?? 0, 0, ',', '.'))
                ->description('Per transaksi')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),
            
            Stat::make('Resep', Sale::whereDate('created_at', today())->where('is_prescription', true)->count())
                ->description('Transaksi dengan resep')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
        ];
    }
    
    protected function getWeeklyChart(): array
    {
        return Sale::query()
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total')
            ->toArray();
    }
}
```

### 8.2 Alerts Widget (Low Stock & Expired)

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\ProductBatch;
use Filament\Widgets\Widget;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AlertsWidget extends Widget implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $view = 'filament.widgets.alerts-widget';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getAlertsQuery())
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'expired' => 'danger',
                        'near_expired' => 'warning',
                        'low_stock' => 'info',
                    }),
                Tables\Columns\TextColumn::make('product_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('batch_number'),
                Tables\Columns\TextColumn::make('expired_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric(),
                Tables\Columns\TextColumn::make('message'),
            ])
            ->defaultSort('expired_date', 'asc')
            ->paginated([5, 10, 25]);
    }

    protected function getAlertsQuery(): Builder
    {
        // Custom query to combine different alert types
        // Implementation depends on your needs
    }
}
```

---

## 9. Key Filament Resources

### 9.1 Product Resource

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Enums\CategoryType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationLabel = 'Produk/Obat';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Produk')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('code')
                                    ->label('Kode SKU')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),
                                    
                                Forms\Components\TextInput::make('barcode')
                                    ->label('Barcode')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),
                                    
                                Forms\Components\Select::make('category_id')
                                    ->label('Kategori')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required(),
                                        Forms\Components\Select::make('type')
                                            ->options(CategoryType::class)
                                            ->required(),
                                    ]),
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Produk')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('generic_name')
                                    ->label('Nama Generik')
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            ]),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(2),
                    ]),
                    
                Forms\Components\Section::make('Satuan & Harga')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('base_unit_id')
                                    ->label('Satuan Dasar')
                                    ->relationship('baseUnit', 'name')
                                    ->required()
                                    ->searchable(),
                                    
                                Forms\Components\TextInput::make('purchase_price')
                                    ->label('Harga Beli')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required(),
                                    
                                Forms\Components\TextInput::make('selling_price')
                                    ->label('Harga Jual')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required(),
                            ]),
                            
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('min_stock')
                                    ->label('Stok Minimum')
                                    ->numeric()
                                    ->default(10),
                                    
                                Forms\Components\TextInput::make('max_stock')
                                    ->label('Stok Maksimum')
                                    ->numeric(),
                                    
                                Forms\Components\TextInput::make('rack_location')
                                    ->label('Lokasi Rak')
                                    ->maxLength(50),
                            ]),
                    ]),
                    
                Forms\Components\Section::make('Pengaturan Khusus')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('requires_prescription')
                                    ->label('Butuh Resep')
                                    ->helperText('Obat keras/resep dokter'),
                                    
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                                    
                                Forms\Components\FileUpload::make('image')
                                    ->label('Gambar')
                                    ->image()
                                    ->directory('products'),
                            ]),
                    ]),
                    
                // Unit Conversions (Repeater)
                Forms\Components\Section::make('Konversi Satuan')
                    ->schema([
                        Forms\Components\Repeater::make('unitConversions')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('from_unit_id')
                                    ->label('Dari Satuan')
                                    ->relationship('fromUnit', 'name')
                                    ->required(),
                                Forms\Components\Select::make('to_unit_id')
                                    ->label('Ke Satuan')
                                    ->relationship('toUnit', 'name')
                                    ->required(),
                                Forms\Components\TextInput::make('conversion_value')
                                    ->label('Nilai Konversi')
                                    ->numeric()
                                    ->required()
                                    ->helperText('Contoh: 1 Box = 10 Strip'),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Konversi'),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular()
                    ->defaultImageUrl(fn () => asset('images/medicine-placeholder.png')),
                    
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record) => $record->generic_name),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('total_stock')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->color(fn (Product $record) => 
                        $record->total_stock <= $record->min_stock ? 'danger' : 'success'
                    ),
                    
                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Harga Jual')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('requires_prescription')
                    ->label('Resep')
                    ->boolean(),
                    
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                    
                Tables\Filters\TernaryFilter::make('requires_prescription')
                    ->label('Butuh Resep'),
                    
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Rendah')
                    ->query(fn (Builder $query) => 
                        $query->whereRaw('total_stock <= min_stock')
                    ),
                    
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('viewBatches')
                    ->label('Lihat Batch')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->url(fn (Product $record) => 
                        ProductBatchResource::getUrl('index', ['product_id' => $record->id])
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BatchesRelationManager::class,
            RelationManagers\StockMovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'code', 'barcode', 'generic_name'];
    }
}
```

---

## 10. Scheduled Tasks (Console Commands)

```php
<?php

// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Check expired products daily at 6 AM
        $schedule->command('apotek:check-expired')
            ->dailyAt('06:00')
            ->withoutOverlapping();
        
        // Check low stock daily at 7 AM
        $schedule->command('apotek:check-low-stock')
            ->dailyAt('07:00')
            ->withoutOverlapping();
        
        // Generate narcotic report monthly (for BPOM compliance)
        $schedule->command('apotek:generate-narcotic-report')
            ->monthlyOn(1, '08:00')
            ->withoutOverlapping();
        
        // Database backup daily at midnight
        $schedule->command('apotek:backup-database')
            ->dailyAt('00:00')
            ->withoutOverlapping();
        
        // Send daily sales report to owner
        $schedule->command('apotek:send-daily-report')
            ->dailyAt('21:00')
            ->withoutOverlapping();
        
        // Clean up old activity logs (older than 90 days)
        $schedule->command('apotek:cleanup-logs --days=90')
            ->weeklyOn(1, '02:00')
            ->withoutOverlapping();
    }
}
```

---

## 11. Installation & Setup Commands

```bash
# Create Laravel project
composer create-project laravel/laravel apotek-pos

# Install Filament
composer require filament/filament:"^4.0"
php artisan filament:install --panels

# Install additional packages
composer require laravel/sanctum           # API Auth
composer require spatie/laravel-permission # Role & Permission
composer require spatie/laravel-activitylog # Activity logging
composer require barryvdh/laravel-dompdf   # PDF generation
composer require maatwebsite/excel         # Excel import/export
composer require mike42/escpos-php         # Thermal printer

# Run migrations
php artisan migrate

# Seed default data
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=UnitSeeder
php artisan db:seed --class=PaymentMethodSeeder
php artisan db:seed --class=DefaultSettingsSeeder

# Create admin user
php artisan make:filament-user
```

---

## 12. Environment Variables

```env
# .env

APP_NAME="ApotekPOS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://apotek.example.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apotek_pos
DB_USERNAME=apotek
DB_PASSWORD=secure_password

# API Rate Limiting
API_RATE_LIMIT=60

# Pharmacy Settings
PHARMACY_EXPIRED_WARNING_DAYS=90
PHARMACY_LOW_STOCK_PERCENTAGE=20
PHARMACY_COMPOUNDING_FEE=5000
PHARMACY_TAX_PERCENTAGE=0

# Backup Settings
BACKUP_DESTINATION=local
BACKUP_KEEP_DAYS=30

# Notification
MAIL_MAILER=smtp
TELEGRAM_BOT_TOKEN=xxx
WHATSAPP_API_KEY=xxx
```

---

## Summary Checklist

### Backend Development Phases:

**Phase 1: Foundation (Week 1)**
- [ ] Laravel 12 setup
- [ ] Database migrations
- [ ] Models & relationships
- [ ] Enums & constants
- [ ] Basic seeders

**Phase 2: Filament Admin (Week 2)**
- [ ] Install & configure Filament 4
- [ ] Master data resources (Products, Categories, Units, Suppliers)
- [ ] Dashboard widgets
- [ ] Role-based access

**Phase 3: Core Business Logic (Week 3)**
- [ ] FEFO Service
- [ ] Sale Service
- [ ] Inventory Service
- [ ] Compounding Service
- [ ] Stock movement tracking

**Phase 4: POS & Sales (Week 4)**
- [ ] POS custom page
- [ ] Sale transactions
- [ ] Prescription handling
- [ ] Cashier shift management
- [ ] Receipt printing

**Phase 5: Purchasing & Returns (Week 5)**
- [ ] Purchase Order system
- [ ] Receiving with batch entry
- [ ] Returns (supplier & customer)
- [ ] Stock opname

**Phase 6: Reports & Compliance (Week 6)**
- [ ] Sales reports
- [ ] Stock reports
- [ ] Narcotic/Psychotropic reports (BPOM compliance)
- [ ] Expired product reports
- [ ] Profit/Loss reports

**Phase 7: API & Integration (Week 7)**
- [ ] REST API for Flutter
- [ ] Authentication (Sanctum)
- [ ] Sync endpoints for offline
- [ ] Webhook notifications

**Phase 8: Polish & Launch (Week 8)**
- [ ] Testing
- [ ] Performance optimization
- [ ] Documentation
- [ ] Deployment setup
