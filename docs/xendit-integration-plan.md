# Xendit Payment Gateway Integration Plan

## Overview

Dokumen ini berisi analisis dan rencana implementasi integrasi Xendit Payment Gateway untuk aplikasi Laravel Apotik. Integrasi bersifat **opsional** - user dapat enable/disable, dan jika disabled, payment tetap manual seperti saat ini.

---

## Analisis Sistem Existing

### Payment Flow Saat Ini
1. **Model Payment Method**: `PaymentMethod` menyimpan metode pembayaran (Tunai, Debit, E-wallet, dll)
2. **Model Sale Payment**: `SalePayment` mencatat pembayaran per transaksi
3. **Proses**:
   - Kasir input jumlah bayar → sistem catat → selesai
   - Semua manual, tidak ada validasi real-time dari payment gateway

### Model Settings
Sudah ada sistem setting dengan isolasi per store:
```php
Setting::get('key', 'default', $storeId);
Setting::set('key', 'value', $storeId, 'group');
```

### Payment Methods (Seeder)
- Tunai (Cash)
- Debit (BCA, Mandiri, BRI, BNI)
- E-wallet (GoPay, OVO, DANA, ShopeePay, LinkAja)
- QRIS
- Transfer Bank

---

## Xendit Integration Scope

### Fitur yang Akan Diintegrasikan

| Fitur | Deskripsi | Priority |
|-------|-----------|----------|
| Invoice API | Buat invoice untuk pembayaran | High |
| E-Wallet | GoPay, OVO, DANA, ShopeePay, LinkAja | High |
| Virtual Account | BCA, Mandiri, BRI, BNI, Permata | Medium |
| QRIS | QR Code payment standar nasional | High |
| Webhook | Notifikasi status pembayaran otomatis | High |

### Alur Pembayaran dengan Xendit

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Kasir     │───▶│  Backend    │───▶│   Xendit    │───▶│  Customer   │
│ (POS/App)   │    │  Laravel    │    │   API       │    │  Bayar      │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
                          │                                      │
                          │         ┌─────────────┐              │
                          │◀────────│   Webhook   │◀─────────────┘
                          │         │  Callback   │
                          │         └─────────────┘
                          ▼
                   ┌─────────────┐
                   │  Update     │
                   │  Payment    │
                   │  Status     │
                   └─────────────┘
```

---

## Technical Requirements

### Dependencies
```bash
composer require xendit/xendit-php:^7.0
```

### Environment Variables
```env
# Xendit Configuration
XENDIT_ENABLED=false
XENDIT_SECRET_KEY=xnd_development_xxxx
XENDIT_PUBLIC_KEY=xnd_public_development_xxxx
XENDIT_WEBHOOK_TOKEN=your_webhook_verification_token
XENDIT_IS_PRODUCTION=false
```

### Config File (`config/xendit.php`)
```php
return [
    'enabled' => env('XENDIT_ENABLED', false),
    'secret_key' => env('XENDIT_SECRET_KEY'),
    'public_key' => env('XENDIT_PUBLIC_KEY'),
    'webhook_token' => env('XENDIT_WEBHOOK_TOKEN'),
    'is_production' => env('XENDIT_IS_PRODUCTION', false),
];
```

---

## Database Changes

### New Migration: `xendit_transactions`
```php
Schema::create('xendit_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sale_id')->nullable()->constrained()->cascadeOnDelete();
    $table->string('external_id')->unique();
    $table->string('xendit_id')->nullable();
    $table->string('invoice_url')->nullable();
    $table->string('payment_method')->nullable(); // EWALLET, QRIS, VIRTUAL_ACCOUNT
    $table->string('payment_channel')->nullable(); // OVO, GOPAY, QRIS, BCA, etc
    $table->decimal('amount', 15, 2);
    $table->string('status'); // PENDING, PAID, EXPIRED, FAILED
    $table->json('xendit_response')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});
```

### Update `settings` table
Tambahkan setting baru untuk Xendit per store:
```
Key: xendit_enabled (boolean)
Key: xendit_secret_key (encrypted)
Key: xendit_webhook_token (encrypted)
Key: xendit_is_production (boolean)
```

---

## New Files to Create

### 1. Service Layer

**`app/Services/XenditService.php`**
```php
class XenditService
{
    public function isEnabled(?int $storeId = null): bool;
    public function testConnection(): array;
    public function createInvoice(Sale $sale, array $options): XenditTransaction;
    public function createQrisPayment(Sale $sale): XenditTransaction;
    public function createEwalletPayment(Sale $sale, string $channel): XenditTransaction;
    public function getInvoiceStatus(string $invoiceId): array;
    public function expireInvoice(string $invoiceId): bool;
}
```

### 2. Model

**`app/Models/XenditTransaction.php`**
- Relasi ke Sale
- Status tracking
- Response logging

### 3. Controllers

**`app/Http/Controllers/Api/V1/XenditSettingController.php`**
```php
// GET /api/v1/settings/xendit - Get Xendit config status
// POST /api/v1/settings/xendit - Save Xendit config
// POST /api/v1/settings/xendit/test - Test API key validity
```

**`app/Http/Controllers/Api/V1/XenditPaymentController.php`**
```php
// POST /api/v1/xendit/invoice - Create invoice
// POST /api/v1/xendit/qris - Create QRIS payment
// POST /api/v1/xendit/ewallet - Create E-wallet payment
// GET /api/v1/xendit/status/{externalId} - Check payment status
```

**`app/Http/Controllers/Webhook/XenditWebhookController.php`**
```php
// POST /webhook/xendit/invoice - Handle invoice callback
// POST /webhook/xendit/payment - Handle payment callback
```

### 4. Form Requests

**`app/Http/Requests/XenditSettingRequest.php`**
**`app/Http/Requests/XenditPaymentRequest.php`**

### 5. Enums

**`app/Enums/XenditPaymentStatus.php`**
```php
enum XenditPaymentStatus: string
{
    case Pending = 'PENDING';
    case Paid = 'PAID';
    case Expired = 'EXPIRED';
    case Failed = 'FAILED';
}
```

**`app/Enums/XenditPaymentMethod.php`**
```php
enum XenditPaymentMethod: string
{
    case Invoice = 'INVOICE';
    case Ewallet = 'EWALLET';
    case Qris = 'QRIS';
    case VirtualAccount = 'VIRTUAL_ACCOUNT';
}
```

---

## API Endpoints

### Settings API

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/settings/xendit` | Get Xendit configuration status |
| POST | `/api/v1/settings/xendit` | Save Xendit configuration |
| POST | `/api/v1/settings/xendit/test` | Test API key validity |

### Payment API

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/xendit/invoice` | Create Xendit invoice |
| POST | `/api/v1/xendit/qris` | Create QRIS payment |
| POST | `/api/v1/xendit/ewallet` | Create E-wallet payment |
| GET | `/api/v1/xendit/status/{id}` | Check payment status |
| POST | `/api/v1/xendit/cancel/{id}` | Cancel/expire payment |

### Webhook (Public, No Auth)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/webhook/xendit/invoice` | Invoice status callback |
| POST | `/webhook/xendit/payment` | Payment status callback |

---

## Test Connection Feature

### Endpoint: `POST /api/v1/settings/xendit/test`

Request:
```json
{
    "secret_key": "xnd_development_xxxxx"
}
```

Response (Success):
```json
{
    "success": true,
    "message": "Koneksi ke Xendit berhasil",
    "data": {
        "balance": 1500000,
        "business_id": "xxx",
        "environment": "development"
    }
}
```

Response (Failed):
```json
{
    "success": false,
    "message": "API Key tidak valid",
    "error": "INVALID_API_KEY"
}
```

### Implementation
```php
public function testConnection(string $secretKey): array
{
    try {
        Configuration::setXenditKey($secretKey);
        $balanceApi = new BalanceApi();
        $balance = $balanceApi->getBalance('CASH');

        return [
            'success' => true,
            'message' => 'Koneksi ke Xendit berhasil',
            'data' => [
                'balance' => $balance->getBalance(),
                'environment' => str_contains($secretKey, 'development')
                    ? 'development' : 'production'
            ]
        ];
    } catch (XenditSdkException $e) {
        return [
            'success' => false,
            'message' => 'API Key tidak valid',
            'error' => $e->getMessage()
        ];
    }
}
```

---

## Webhook Security

### Verification
```php
public function verifyWebhook(Request $request): bool
{
    $callbackToken = $request->header('X-CALLBACK-TOKEN');
    $expectedToken = config('xendit.webhook_token');

    return hash_equals($expectedToken, $callbackToken ?? '');
}
```

### Middleware
```php
class VerifyXenditWebhook
{
    public function handle(Request $request, Closure $next)
    {
        if (!$this->verifyWebhook($request)) {
            return response()->json(['error' => 'Invalid callback token'], 401);
        }

        return $next($request);
    }
}
```

---

## Integration with Existing POS Flow

### Scenario 1: Xendit Disabled (Current Flow)
```
Kasir → Input Payment → Save to SalePayment → Done
```

### Scenario 2: Xendit Enabled - Cash Payment
```
Kasir → Select Cash → Input Amount → Save to SalePayment → Done
(Same as current, no Xendit involved)
```

### Scenario 3: Xendit Enabled - QRIS/E-wallet
```
Kasir → Select QRIS/E-wallet → Create Xendit Invoice
      → Show QR/Redirect Link → Customer Pays
      → Webhook Callback → Update SalePayment → Done
```

---

## Implementation Status

### Phase 1: Foundation - COMPLETED
- [x] Install xendit/xendit-php package
- [x] Create config/xendit.php
- [x] Add environment variables to .env.example
- [x] Create migration for xendit_transactions table
- [x] Create XenditTransaction model
- [x] Create XenditPaymentStatus enum
- [x] Create XenditPaymentMethod enum

### Phase 2: Service Layer - COMPLETED
- [x] Create XenditService class
- [x] Implement testConnection() method
- [x] Implement createInvoice() method
- [x] Implement getPaymentStatus() method
- [x] Implement expirePayment() method
- [x] Implement handleWebhook() method

### Phase 3: Settings API - COMPLETED
- [x] Create XenditSettingController
- [x] Implement GET settings endpoint
- [x] Implement POST test connection endpoint
- [x] Add routes to api.php

### Phase 4: Payment API - COMPLETED
- [x] Create XenditPaymentController (POS Web)
- [x] Implement create invoice endpoint
- [x] Implement check status endpoint
- [x] Implement cancel endpoint
- [x] Add routes to web.php

### Phase 5: Webhook Handler - COMPLETED
- [x] Create VerifyXenditWebhook middleware
- [x] Create XenditWebhookController
- [x] Implement invoice callback handler
- [x] Add webhook routes (public, no auth)

### Phase 6: Dashboard & UI - COMPLETED
- [x] Create Filament settings page (XenditSettings)
- [x] Update POS Web transaction view with Xendit option
- [x] Add Xendit payment modal with status polling

### Phase 7: Testing - COMPLETED
- [x] Test connection with Xendit sandbox/development mode

---

## Configuration (Sudah Ditentukan)

| Setting | Value |
|---------|-------|
| Scope | Global (single store/apotek) |
| Payment Methods | Semua (QRIS, E-wallet, Virtual Account) |
| Invoice Duration | 1 jam |
| Public Key | `xnd_public_development_ViCOkWpzIj_pvFWSFVdxekbu54d1803Z5jINhhI52PDvp4YCNDXx_rwpCGkF8OEz` |
| Webhook Token | `Xtwzb7frnI6LhrBenNJmk4WeKyiBa5CVcwnikjMy8TB425Ty` |

### PENTING: Secret Key Dibutuhkan

> **Secret Key** belum tersedia. Public Key hanya untuk frontend/client-side.
> Untuk API calls dari backend, dibutuhkan **Secret Key** dari Xendit Dashboard.
>
> Cara mendapatkan:
> 1. Login ke [Xendit Dashboard](https://dashboard.xendit.co)
> 2. Settings → Developers → API Keys
> 3. Copy **Secret Key** (format: `xnd_development_xxx...` tanpa "public")

---

## References

- [Xendit PHP SDK](https://github.com/xendit/xendit-php)
- [Xendit API Reference](https://developers.xendit.co/api-reference/)
- [Xendit Invoice API](https://github.com/xendit/xendit-php/blob/master/docs/InvoiceApi.md)
- [Xendit Webhook Documentation](https://docs.xendit.co/docs/handling-webhooks)

---

## Notes

- Gunakan mode **development/sandbox** untuk testing
- API Key development diawali dengan `xnd_development_`
- API Key production diawali dengan `xnd_production_`
- Webhook token bisa didapat dari Xendit Dashboard → Settings → Webhooks
