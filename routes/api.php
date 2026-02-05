<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CashierShiftController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DoctorController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\StoreController;
use App\Http\Controllers\Api\V1\UnitController;
use App\Http\Controllers\Api\V1\XenditPaymentController;
use App\Http\Controllers\Api\V1\XenditSettingController;
use App\Http\Controllers\Webhook\XenditWebhookController;
use App\Http\Middleware\VerifyXenditWebhook;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Apotek POS API v1 Routes
|
*/

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);

        // Dashboard
        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
        Route::get('/dashboard/low-stock', [DashboardController::class, 'lowStockProducts']);
        Route::get('/dashboard/expiring', [DashboardController::class, 'expiringBatches']);

        // Cashier Shift
        Route::get('/shift/current', [CashierShiftController::class, 'current']);
        Route::post('/shift/open', [CashierShiftController::class, 'open']);
        Route::post('/shift/close', [CashierShiftController::class, 'close']);
        Route::get('/shift/summary', [CashierShiftController::class, 'summary']);
        Route::get('/shift/sales', [CashierShiftController::class, 'sales']);

        // Products
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::post('/products/barcode', [ProductController::class, 'searchByBarcode']);

        // Categories
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/category-types', [CategoryController::class, 'types']);

        // Units
        Route::get('/units', [UnitController::class, 'index']);

        // Payment Methods
        Route::get('/payment-methods', [PaymentMethodController::class, 'index']);

        // Doctors
        Route::get('/doctors', [DoctorController::class, 'index']);
        Route::get('/doctors/{doctor}', [DoctorController::class, 'show']);
        Route::post('/doctors', [DoctorController::class, 'store']);

        // Store & Settings
        Route::get('/store', [StoreController::class, 'info']);
        Route::get('/settings', [StoreController::class, 'settings']);

        // Customers
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{customer}', [CustomerController::class, 'show']);
        Route::post('/customers', [CustomerController::class, 'store']);

        // Sales
        Route::get('/sales', [SaleController::class, 'index']);
        Route::get('/sales/{sale}', [SaleController::class, 'show']);
        Route::post('/sales', [SaleController::class, 'store']);
        Route::post('/sales/{sale}/void', [SaleController::class, 'void']);
        Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt']);

        // Reports
        Route::get('/reports/sales', [ReportController::class, 'salesSummary']);

        // Xendit Settings
        Route::prefix('xendit')->group(function () {
            Route::get('/settings', [XenditSettingController::class, 'index']);
            Route::post('/settings/test', [XenditSettingController::class, 'test']);

            // Xendit Payment API
            Route::get('/status', [XenditPaymentController::class, 'status']);
            Route::post('/invoice', [XenditPaymentController::class, 'createInvoice']);
            Route::post('/sale', [XenditPaymentController::class, 'createSaleWithPayment']);
            Route::get('/check/{transaction}', [XenditPaymentController::class, 'checkStatus']);
            Route::post('/cancel/{transaction}', [XenditPaymentController::class, 'cancel']);
            Route::get('/transactions', [XenditPaymentController::class, 'transactions']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Xendit Webhook Routes (Public - No Auth)
|--------------------------------------------------------------------------
*/
Route::prefix('webhook/xendit')->middleware(VerifyXenditWebhook::class)->group(function () {
    Route::post('/invoice', [XenditWebhookController::class, 'handleInvoice']);
});
