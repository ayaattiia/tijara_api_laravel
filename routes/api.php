<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TypeCategorieController;

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\DeliveriesController;
use App\Http\Controllers\Api\InvoicesController;
use App\Http\Controllers\Api\PreInvoiceController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\PaymentsController;
use App\Http\Controllers\Api\NotificationsController;
use App\Http\Controllers\Api\ReviewsController;
use App\Http\Controllers\Api\ReportsController;
use App\Http\Controllers\Api\TransportsController;


// AUTH


Route::prefix('auth')->group(function () {
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
    });
});

// TYPE CATEGORIES


Route::prefix('typecategories')->group(function () {

    // Public reads
    Route::get('/',       [TypeCategorieController::class, 'index']);
    Route::get('/all',    [TypeCategorieController::class, 'all']);
    Route::get('/{id}',   [TypeCategorieController::class, 'show']);

    // Protected writes (admin)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/',              [TypeCategorieController::class, 'store']);
        Route::put('/{id}',           [TypeCategorieController::class, 'update']);
        Route::patch('/{id}/toggle',  [TypeCategorieController::class, 'toggle']);
        Route::delete('/{id}',        [TypeCategorieController::class, 'destroy']);
    });
});

// CATEGORIES


Route::prefix('categories')->group(function () {

    // Public reads
    Route::get('/',        [CategoryController::class, 'index']);
    Route::get('/all',     [CategoryController::class, 'all']);
    Route::get('/types',   [CategoryController::class, 'types']);
    Route::get('/{id}',    [CategoryController::class, 'show']);

    // Protected writes (admin)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/',             [CategoryController::class, 'store']);
        Route::put('/{id}',          [CategoryController::class, 'update']);
        Route::patch('/{id}/toggle', [CategoryController::class, 'toggle']);
        Route::delete('/{id}',       [CategoryController::class, 'destroy']);
    });
});


// ADS (ANNONCES)


Route::get('/annonces',              [AdController::class, 'index']);
Route::get('/annonces/{id}',         [AdController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/annonces',                [AdController::class, 'store']);
    Route::post('/annonces/{id}/like',      [AdController::class, 'like']);
    Route::post('/annonces/{id}/comments',  [AdController::class, 'comment']);
});


// PRODUCTS


Route::get('/products',                         [ProductController::class, 'index']);
Route::get('/products/vendor/{vendorId}',        [ProductController::class, 'vendorProfile']);
Route::get('/products/{id}',                    [ProductController::class, 'show']);
Route::get('/products/{id}/reviews',            [ProductController::class, 'reviews']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products/mine',                        [ProductController::class, 'mine']);
    Route::post('/products',                            [ProductController::class, 'store']);
    Route::put('/products/{id}',                        [ProductController::class, 'update']);
    Route::patch('/products/{id}/status',               [ProductController::class, 'toggleStatus']);
    Route::delete('/products/{id}',                     [ProductController::class, 'destroy']);
    Route::post('/products/{id}/reviews',               [ProductController::class, 'addReview']);
    Route::delete('/products/{id}/reviews/{reviewId}',  [ProductController::class, 'deleteReview']);
});


// TRANSPORTS


Route::get('/transports',       [TransportsController::class, 'index']);
Route::get('/transports/{id}',  [TransportsController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transports',              [TransportsController::class, 'store']);
    Route::put('/transports/{id}',          [TransportsController::class, 'update']);
    Route::patch('/transports/{id}/toggle', [TransportsController::class, 'toggle']);
    Route::delete('/transports/{id}',       [TransportsController::class, 'destroy']);
});

// REVIEWS
// NOTE: /summary and /my MUST come before /{id} to avoid wildcard collision



Route::get('/reviews',          [ReviewsController::class, 'index']);
Route::get('/reviews/summary',  [ReviewsController::class, 'summary']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reviews/my',       [ReviewsController::class, 'myReviews']);
    Route::post('/reviews',         [ReviewsController::class, 'store']);
    Route::delete('/reviews/{id}',  [ReviewsController::class, 'destroy']);
});


// ORDERS


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders',                   [OrdersController::class, 'index']);
    Route::get('/orders/{id}',              [OrdersController::class, 'show']);
    Route::post('/orders',                  [OrdersController::class, 'store']);
    Route::patch('/orders/{id}/status',     [OrdersController::class, 'updateStatus']);
});



// INVOICES
// NOTE: /from-order/{idOrder} MUST come before /{id}

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/invoices/{id}',           [InvoicesController::class, 'update']);
    Route::patch('/invoices/{id}/cancel',  [InvoicesController::class, 'cancel']);
    Route::get('/invoices/{id}/pdf',       [InvoicesController::class, 'pdf']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/invoices',                             [InvoicesController::class, 'index']);
    Route::post('/invoices/from-order/{idOrder}',       [InvoicesController::class, 'fromOrder']);
    Route::get('/invoices/{id}',                        [InvoicesController::class, 'show']);
    Route::patch('/invoices/{id}/paid',                 [InvoicesController::class, 'markPaid']);
});

// PAYMENTS
// NOTE: /mine MUST come before the generic GET /payments (admin list)


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/payments/mine',            [PaymentsController::class, 'mine']);
    Route::get('/payments',                 [PaymentsController::class, 'index']);
    Route::post('/payments',                [PaymentsController::class, 'store']);
    Route::post('/payments/{id}/refund',    [PaymentsController::class, 'refund']);
});


// DELIVERIES


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/deliveries',               [DeliveriesController::class, 'index']);
    Route::post('/deliveries',              [DeliveriesController::class, 'store']);
    Route::put('/deliveries/{id}',          [DeliveriesController::class, 'update']);
    Route::patch('/deliveries/{id}/status', [DeliveriesController::class, 'updateStatus']);
});


// NOTIFICATIONS
// NOTE: /read-all MUST come before /{id}/read

Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/notifications/{id}', [NotificationsController::class, 'destroy']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications',                    [NotificationsController::class, 'index']);
    Route::patch('/notifications/read-all',         [NotificationsController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read',        [NotificationsController::class, 'markRead']);
});

// REPORTS  (auth:sanctum — role checks handled inside each method)

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reports/overview',         [ReportsController::class, 'overview']);
    Route::get('/reports/sales-by-month',   [ReportsController::class, 'salesByMonth']);
    Route::get('/reports/top-products',     [ReportsController::class, 'topProducts']);
    Route::get('/reports/top-customers',    [ReportsController::class, 'topCustomers']);
});
// ── SUPPLIERS (fournisseurs) ─────────────────────────────────────
Route::get('/suppliers',                  [SupplierController::class, 'index']);
Route::get('/suppliers/{id}',             [SupplierController::class, 'show']);
Route::get('/suppliers/{id}/products',    [SupplierController::class, 'products']);
Route::get('/suppliers/{id}/reviews',     [SupplierController::class, 'reviews']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/suppliers/{id}/history', [SupplierController::class, 'history']);
    Route::put('/suppliers/{id}',         [SupplierController::class, 'update']);
});

// ── PRE-INVOICES ──────────────────────────────────────────────────
// NOTE: /pre-invoices/{id}/pdf etc. MUST come after the literal action routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pre-invoices',                    [PreInvoiceController::class, 'index']);
    Route::post('/pre-invoices',                   [PreInvoiceController::class, 'store']);
    Route::get('/pre-invoices/{id}',                [PreInvoiceController::class, 'show']);
    Route::put('/pre-invoices/{id}',                [PreInvoiceController::class, 'update']);
    Route::post('/pre-invoices/{id}/submit',        [PreInvoiceController::class, 'submit']);
    Route::post('/pre-invoices/{id}/approve',       [PreInvoiceController::class, 'approve']);
    Route::post('/pre-invoices/{id}/reject',        [PreInvoiceController::class, 'reject']);
    Route::post('/pre-invoices/{id}/convert',       [PreInvoiceController::class, 'convert']);
    Route::get('/pre-invoices/{id}/pdf',            [PreInvoiceController::class, 'pdf']);
});
// DEALS
// NOTE: /deals/{id}/reviews, /stock, /status, /print, /barcode, /qrcode
// MUST come after the CRUD routes where appropriate.

Route::middleware('auth:sanctum')->group(function () {

    // CRUD
    Route::get('/deals',                          [DealController::class, 'index']);
    Route::post('/deals',                         [DealController::class, 'store']);
    Route::get('/deals/{id}',                     [DealController::class, 'show']);
    Route::put('/deals/{id}',                     [DealController::class, 'update']);
    Route::delete('/deals/{id}',                  [DealController::class, 'destroy']);

    // Product Status
    Route::patch('/deals/{id}/status',            [DealController::class, 'updateStatus']);

    // Stock Management
    Route::patch('/deals/{id}/stock',             [DealController::class, 'updateStock']);

    // Vendor Products
    Route::get('/vendors/{id}/deals',             [DealController::class, 'vendorDeals']);

    // Reviews
    Route::get('/deals/{id}/reviews',             [DealController::class, 'reviews']);
    Route::post('/deals/{id}/reviews',            [DealController::class, 'addReview']);
    Route::delete('/deals/{id}/reviews/{review}', [DealController::class, 'deleteReview']);

    // Product Printing
    Route::get('/deals/{id}/print',               [DealController::class, 'print']);

    // Barcode
    Route::get('/deals/{id}/barcode',             [DealController::class, 'barcode']);

    // QR Code
    Route::get('/deals/{id}/qrcode',              [DealController::class, 'qrcode']);

    // Product URLs
    Route::get('/deals/{id}/url',                 [DealController::class, 'url']);
});
