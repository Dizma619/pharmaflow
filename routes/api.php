<?php
use App\Http\Controllers\Api\AnalyticsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\ShelfController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CashflowController;
use App\Http\Controllers\Api\CashflowController as ApiCashflowController;
use App\Http\Controllers\Api\CashflowController as ControllersApiCashflowController;
use App\Http\Controllers\Api\CashflowController as HttpControllersApiCashflowController;
use App\Http\Controllers\Api\StockMonitoringController;

Route::prefix('v1')->group(function () {

    // ================================================
    // PUBLIC ROUTES (No Auth Required)
    // ================================================

    // Authentication
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/validate', [AuthController::class, 'validateToken']);

    // Public Product Routes (E-commerce)
    Route::get('medicines', [MedicineController::class, 'index']);
    Route::get('medicines/{id}', [MedicineController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    Route::get('promotions', [PromotionController::class, 'index']);
// 📦 Manajemen Stok Dasar
    Route::get('/stocks', [StockController::class, 'index']);
    Route::get('/stocks/{id}', [StockController::class, 'show']);
    Route::post('/stocks', [StockController::class, 'store']);     // Untuk tombol Tambah Stok
    Route::delete('/stocks/{id}', [StockController::class, 'destroy']); // Untuk tombol Hapus

    // 🚀 Fitur Lanjutan (Sesuai Controller King)
    Route::post('/stocks/adjustment', [StockController::class, 'adjustment']);
    Route::post('/stocks/opname', [StockController::class, 'opname']);
    
    // ⚠️ Filter Stok (Peringatan & Kedaluwarsa)
    Route::get('/stocks-filter/low-stock', [StockController::class, 'lowStock']);
    Route::get('/stocks-filter/expired', [StockController::class, 'expired']);
    Route::get('/stocks-filter/expiring-soon', [StockController::class, 'expiringSoon']);
    Route::get('/suppliers', [\App\Http\Controllers\Api\SupplierController::class, 'index']);
    Route::post('/suppliers', [\App\Http\Controllers\Api\SupplierController::class, 'store']);
    Route::delete('/suppliers/{id}', [\App\Http\Controllers\Api\SupplierController::class, 'destroy']);
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::get('/notifications/stats', [\App\Http\Controllers\Api\NotificationController::class, 'stats']);
    Route::put('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::put('/notifications/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
});

    // ================================================
    // GUEST CHECKOUT - PUBLIC
    // ================================================

    Route::post('orders', [OrderController::class, 'store']);
    Route::post('orders/track', [OrderController::class, 'trackByPhone']);

    Route::post(
        'payments/snap-token',
        [PaymentController::class, 'createSnapToken']
    );

    Route::get(
        'payments/{id}/status',
        [PaymentController::class, 'status']
    );

    Route::post(
        'payments/cod/confirm',
        [PaymentController::class, 'confirmCOD']
    );

    Route::post(
        'vouchers/validate',
        [VoucherController::class, 'validate']
    );

    // Midtrans Webhook
    Route::post(
        'webhook/midtrans',
        [PaymentController::class, 'notification']
    );

    // ================================================
    // PROTECTED ROUTES (JWT Required)
    // ================================================

    Route::middleware('jwt')->group(function () {

        // FIXED: Ditarik ke atas agar bisa diakses admin, staff, maupun owner tanpa tertimpa
        Route::get('dashboard/summary', [DashboardController::class, 'summary'])->middleware('role:admin,staff,owner');

        Route::get(
            'stocks/check-expired',
            [
                StockMonitoringController::class,
                'checkExpiringMedicine'
            ]
        );
        
        // ============================================
        // AUTH ROUTES
        // ============================================

        Route::post(
            'auth/logout',
            [AuthController::class, 'logout']
        );

        Route::post(
            'auth/refresh',
            [AuthController::class, 'refresh']
        );

        Route::get(
            'auth/me',
            [AuthController::class, 'me']
        );

        // ============================================
        // NOTIFICATIONS (ALL ROLES)
        // ============================================

        Route::get(
            'notifications',
            [NotificationController::class, 'index']
        );

        Route::get(
            'notifications/stats',
            [NotificationController::class, 'stats']
        );

        Route::get(
            'notifications/{id}',
            [NotificationController::class, 'show']
        );

        Route::post(
            'notifications/{id}/read',
            [NotificationController::class, 'markAsRead']
        );

        Route::post(
            'notifications/mark-all-read',
            [NotificationController::class, 'markAllAsRead']
        );

        // ============================================
        // CUSTOMER ROUTES
        // ============================================

        Route::middleware('role:customer')->group(function () {

            Route::get('orders', [
                OrderController::class,
                'index'
            ]);

            Route::get('orders/{id}', [
                OrderController::class,
                'show'
            ]);

            Route::put('orders/{id}', [
                OrderController::class,
                'update'
            ]);

            Route::post('orders/{id}/cancel', [
                OrderController::class,
                'cancel'
            ]);

            // Promotions
            Route::get('promotions', [
                PromotionController::class,
                'index'
            ]);

            Route::get('promotions/{id}', [
                PromotionController::class,
                'show'
            ]);
        });

        // ============================================
        // STAFF ROUTES
        // ============================================

        Route::middleware('role:staff')->group(function () {

            // TRANSACTIONS
            Route::post(
                'transactions/calculate-change',
                [
                    TransactionController::class,
                    'calculateChange'
                ]
            );

            Route::apiResource(
                'transactions',
                TransactionController::class
            );

            Route::post(
                'transactions/{id}/complete',
                [TransactionController::class, 'complete']
            );

            Route::get(
                'reports/daily',
                [TransactionController::class, 'dailyReport']
            );

            // POS SEARCH
            Route::get(
                'medicines/search/{code}',
                [MedicineController::class, 'searchByCode']
            );

            // ATTENDANCE
            Route::post(
                'attendance/check-in',
                [AttendanceController::class, 'checkIn']
            );

            Route::post(
                'attendance/check-out',
                [AttendanceController::class, 'checkOut']
            );

            Route::apiResource(
                'attendance',
                AttendanceController::class
            );

            Route::get(
                'attendance/today',
                [AttendanceController::class, 'today']
            );

            Route::get(
                'attendance/monthly-report',
                [AttendanceController::class, 'monthlyReport']
            );

            // MASTER DATA
            Route::apiResource(
                'medicines',
                MedicineController::class
            );

            Route::apiResource(
                'categories',
                CategoryController::class
            );

            Route::apiResource(
                'suppliers',
                SupplierController::class
            );

            Route::apiResource(
                'warehouses',
                WarehouseController::class
            );

            Route::apiResource(
                'shelves',
                ShelfController::class
            );

            // STOCK
            Route::get(
                'stocks',
                [StockController::class, 'index']
            );

            Route::get(
                'stocks/{id}',
                [StockController::class, 'show']
            );

            Route::get(
                'stocks/low-stock',
                [StockController::class, 'lowStock']
            );

            Route::get(
                'stocks/expired',
                [StockController::class, 'expired']
            );

            Route::get(
                'stocks/expiring-soon',
                [StockController::class, 'expiringSoon']
            );

            Route::post(
                'stocks/adjustment',
                [StockController::class, 'adjustment']
            );

            Route::post(
                'stocks/opname',
                [StockController::class, 'opname']
            );

            // PURCHASES
            Route::apiResource(
                'purchases',
                PurchaseController::class
            );

            Route::post(
                'purchases/{id}/receive',
                [PurchaseController::class, 'receive']
            );

            // ORDERS
            Route::get(
                'orders',
                [OrderController::class, 'index']
            );

            Route::get(
                'orders/{id}',
                [OrderController::class, 'show']
            );

            Route::put(
                'orders/{id}',
                [OrderController::class, 'update']
            );

            // EMPLOYEES
            Route::apiResource(
                'employees',
                EmployeeController::class
            );

            Route::get(
                'employees/{id}/attendance-report',
                [EmployeeController::class, 'attendanceReport']
            );

            // PROMOTIONS & VOUCHERS
            Route::apiResource(
                'promotions',
                PromotionController::class
            );

            Route::apiResource(
                'vouchers',
                VoucherController::class
            );

            // FIXED: Duplikasi dashboard/summary di grup ini sudah dihapus

            Route::get(
                'reports/sales',
                [DashboardController::class, 'salesReport']
            );

            Route::get(
                'reports/profit',
                [DashboardController::class, 'profitReport']
            );

            Route::get(
                'reports/inventory',
                [DashboardController::class, 'inventoryReport']
            );

            // CASHFLOW
            Route::get(
                'cashflow',
                [CashflowController::class, 'index']
            );

            Route::get(
                'cashflow/summary',
                [CashflowController::class, 'summary']
            );

            Route::get(
                'cashflow/by-category',
                [CashflowController::class, 'byCategory']
            );

            Route::get(
                'cashflow/daily-trend',
                [CashflowController::class, 'dailyTrend']
            );

            Route::get(
                'cashflow/top-categories',
                [CashflowController::class, 'topCategories']
            );

            Route::get(
                'cashflow/export',
                [CashflowController::class, 'export']
            );

            Route::post(
                'cashflow',
                [CashflowController::class, 'store']
            );

            Route::put(
                'cashflow/{id}',
                [CashflowController::class, 'update']
            );

            Route::delete(
                'cashflow/{id}',
                [CashflowController::class, 'destroy']
            );
        });

        // ============================================
        // OWNER ROUTES
        // ============================================

        Route::middleware('role:owner')->group(function () {

        // ============================================
            // ORDERS (AKSES UNTUK OWNER)
            // ============================================
            Route::get(
                'orders',
                [OrderController::class, 'index']
            );

            Route::get(
                'orders/{id}',
                [OrderController::class, 'show']
            );

            Route::put(
                'orders/{id}',
                [OrderController::class, 'update']
            );
            
            // FIXED: Duplikasi dashboard/summary di grup ini sudah dihapus

            Route::get(
                'dashboard/analytics',
                [DashboardController::class, 'analytics']
            );

            /**
             * PART 4.4 — OWNER DASHBOARD PRO
             */
            Route::get(
                'dashboard/kpi',
                [DashboardController::class, 'kpi']
            );

            Route::get(
                'dashboard/charts',
                [DashboardController::class, 'charts']
            );

            Route::get(
                'dashboard/top-medicines',
                [DashboardController::class, 'topMedicines']
            );

            Route::get(
                'dashboard/warnings',
                [DashboardController::class, 'warnings']
            );

            Route::get(
                'dashboard/analytics-owner',
                [DashboardController::class, 'analyticsOwner']
            );
            Route::get('analytics/summary', [AnalyticsController::class, 'getSummary']);

            // REPORTS
            Route::get(
                'reports/financial',
                [DashboardController::class, 'financialReport']
            );

            Route::get(
                'reports/cashflow',
                [DashboardController::class, 'cashflowReport']
            );

            Route::get(
                'reports/product-performance',
                [DashboardController::class, 'productPerformance']
            );

            // CASHFLOW
            Route::get(
                'cashflow',
                [CashflowController::class, 'index']
            );

            Route::get(
                'cashflow/summary',
                [CashflowController::class, 'summary']
            );

            Route::get(
                'cashflow/by-category',
                [CashflowController::class, 'byCategory']
            );

            Route::get(
                'cashflow/daily-trend',
                [CashflowController::class, 'dailyTrend']
            );

            Route::get(
                'cashflow/top-categories',
                [CashflowController::class, 'topCategories']
            );

            Route::get(
                'cashflow/export',
                [CashflowController::class, 'export']
            );

            Route::post(
                'cashflow',
                [CashflowController::class, 'store']
            );

            Route::put(
                'cashflow/{id}',
                [CashflowController::class, 'update']
            );

            Route::delete(
                'cashflow/{id}',
                [CashflowController::class, 'destroy']
            );
        });
    });
});