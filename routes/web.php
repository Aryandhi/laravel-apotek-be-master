<?php

use App\Http\Controllers\Pos\AuthController;
use App\Http\Controllers\Pos\CustomerController;
use App\Http\Controllers\Pos\DashboardController;
use App\Http\Controllers\Pos\ProductController;
use App\Http\Controllers\Pos\ReceiptController;
use App\Http\Controllers\Pos\SettingsController;
use App\Http\Controllers\Pos\ShiftController;
use App\Http\Controllers\Pos\TransactionController;
use App\Http\Controllers\Pos\XenditPaymentController;
use App\Http\Middleware\EnsureUserCanAccessPos;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

// POS Routes
Route::prefix('pos')->name('pos.')->group(function () {
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    });

    // Authenticated routes
    Route::middleware(['auth', EnsureUserCanAccessPos::class])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Shift management
        Route::prefix('shift')->name('shift.')->group(function () {
            Route::get('/', [ShiftController::class, 'index'])->name('index');
            Route::get('/open', [ShiftController::class, 'create'])->name('create');
            Route::post('/open', [ShiftController::class, 'store'])->name('store');
            Route::get('/close', [ShiftController::class, 'showClose'])->name('close');
            Route::post('/close', [ShiftController::class, 'close']);
            Route::get('/{shift}/report', [ShiftController::class, 'report'])->name('report');
        });

        // Product catalog
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/search', [ProductController::class, 'search'])->name('search');
            Route::get('/barcode', [ProductController::class, 'searchBarcode'])->name('barcode');
            Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        });

        // Transactions
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [TransactionController::class, 'index'])->name('index');
            Route::get('/new', [TransactionController::class, 'create'])->name('create');
            Route::post('/', [TransactionController::class, 'store'])->name('store');
            Route::get('/history', [TransactionController::class, 'history'])->name('history');
            Route::get('/products', [TransactionController::class, 'getProducts'])->name('products');
            Route::get('/product/{product}/batches', [TransactionController::class, 'getProductBatches'])->name('product.batches');
            Route::get('/{sale}', [TransactionController::class, 'show'])->name('show');
        });

        // Receipts
        Route::prefix('receipts')->name('receipts.')->group(function () {
            Route::get('/{sale}', [ReceiptController::class, 'show'])->name('show');
            Route::get('/{sale}/print', [ReceiptController::class, 'print'])->name('print');
            Route::get('/{sale}/escpos', [ReceiptController::class, 'escpos'])->name('escpos');
            Route::get('/{sale}/escpos-json', [ReceiptController::class, 'escposJson'])->name('escpos.json');
        });

        // Customers
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/search', [CustomerController::class, 'search'])->name('search');
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        });

        // Xendit Payments
        Route::prefix('xendit')->name('xendit.')->group(function () {
            Route::get('/enabled', [XenditPaymentController::class, 'isEnabled'])->name('enabled');
            Route::post('/invoice', [XenditPaymentController::class, 'createInvoice'])->name('invoice');
            Route::get('/status/{transaction}', [XenditPaymentController::class, 'checkStatus'])->name('status');
            Route::post('/cancel/{transaction}', [XenditPaymentController::class, 'cancel'])->name('cancel');
        });

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/printer', [SettingsController::class, 'printer'])->name('printer');
        });
    });
});
