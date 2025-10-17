<?php

use Illuminate\Support\Facades\Route;

// === Controllers (v1) ===
use App\Http\Controllers\Api\v1\{
    AuthController,
    HealthController,
    NotificationController
};

use App\Http\Controllers\Api\v1\CaptchaController;

use App\Http\Controllers\Api\v1\Manager\{
    BuildingController,
    ElevatorController,
    ContractController,
    TaskController as ManagerTaskController
};

use App\Http\Controllers\Api\v1\Tech\{
    TechTaskController,
    WorklogController
};

use App\Http\Controllers\Api\v1\Warehouse\{
    ItemController,
    StockController,
    PartRequestController as WarehousePartRequestController,
    PurchaseOrderController
};

use App\Http\Controllers\Api\v1\Timesheet\TimesheetController;
use App\Http\Controllers\Api\v1\Telemetry\TelemetryController;

use App\Http\Controllers\Api\v1\Billing\{
    InvoiceController,
    PaymentController
};

use App\Http\Controllers\Api\v1\BuildingManager\BmController;

use App\Http\Controllers\Api\v1\Admin\{
    UserAdminController,
    TenantAdminController,
    PlanController,
    SubscriptionController,
    SettingController,
    ReportController,
    LogController,
    DashboardController
};

// Auth\LoginOptionsController برای برگرداندن وضعیت UI
use App\Http\Controllers\Api\v1\Auth\LoginOptionsController;

// ----------------------------------------------------
// Health Check
// ----------------------------------------------------
Route::get('/healthz', [HealthController::class, 'index'])->name('healthz');

// ----------------------------------------------------
// Auth (Login/Logout/Refresh + 2FA-SMS Flow)
// ----------------------------------------------------
Route::prefix('auth')->group(function () {

    // UI Login Options (public, lightweight)
    Route::get('/login/options', [LoginOptionsController::class, 'show'])
        ->name('auth.login.options')
        ->middleware('throttle:60,1');

    // Step 1: email/password (+captcha). If SMS enabled => challenge_required
    Route::post('/login', [AuthController::class, 'login'])
        ->name('auth.login')
        ->middleware('throttle:10,1');

    // Step 2: verify SMS code for an issued challenge_id
    Route::post('/login/verify', [AuthController::class, 'verifyChallenge'])
        ->name('auth.login.verify')
        ->middleware('throttle:20,2');

    // Optional: resend SMS code for an existing challenge_id
    Route::post('/login/resend', [AuthController::class, 'resendChallenge'])
        ->name('auth.login.resend')
        ->middleware('throttle:3,5');

    // Token refresh (if applicable)
    Route::post('/refresh', [AuthController::class, 'refresh'])
        ->name('auth.refresh')
        ->middleware('throttle:30,1');

    // Logout (requires auth)
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout')
        ->middleware('auth:sanctum');
});

// ----------------------------------------------------
// CAPTCHA
// ----------------------------------------------------
Route::prefix('captcha')->group(function () {
    Route::post('/generate', [CaptchaController::class, 'generate'])
        ->name('captcha.generate')
        ->middleware('throttle:30,1');

    Route::post('/verify', [CaptchaController::class, 'verify'])
        ->name('captcha.verify')
        ->middleware('throttle:60,1');
});

// ----------------------------------------------------
// Authenticated area
// ----------------------------------------------------
Route::middleware(['auth:sanctum'])->group(function () {

    // Self (User)
    Route::get('/users/me', [AuthController::class, 'me'])->name('users.me');
    Route::patch('/users/me', [AuthController::class, 'updateMe'])->name('users.me.update');
    Route::post('/users/me/password', [AuthController::class, 'updatePassword'])->name('users.me.password');

    // Manager
    Route::prefix('manager')->middleware('role:OWNER,INTERNAL_MANAGER')->group(function () {
        Route::apiResource('buildings', BuildingController::class);
        Route::apiResource('elevators', ElevatorController::class);
        Route::apiResource('contracts', ContractController::class);
        Route::apiResource('tasks', ManagerTaskController::class);
        Route::post('tasks/{task}/assign', [ManagerTaskController::class, 'assign']);
    });

    // Tech
    Route::prefix('tech')->middleware('role:TECH')->group(function () {
        Route::get('tasks', [TechTaskController::class, 'index']);
        Route::patch('tasks/{task}/status', [TechTaskController::class, 'updateStatus']);
        Route::post('tasks/{task}/worklogs', [WorklogController::class, 'store']);
        Route::get('worklogs', [WorklogController::class, 'index']);
        Route::post('tasks/{task}/part-requests', [WarehousePartRequestController::class, 'storeFromTech']);
    });

    // Warehouse
    Route::prefix('warehouse')->middleware('role:WAREHOUSE,OWNER,INTERNAL_MANAGER')->group(function () {
        Route::get('items', [ItemController::class, 'index']);
        Route::post('items', [ItemController::class, 'store']);
        Route::patch('items/{item}', [ItemController::class, 'update']);
        Route::delete('items/{item}', [ItemController::class, 'destroy']);
        Route::post('items/{item}/adjust', [StockController::class, 'adjust']);
        Route::get('requests', [WarehousePartRequestController::class, 'index']);
        Route::patch('requests/{partRequest}', [WarehousePartRequestController::class, 'updateStatus']);
        Route::get('purchase-orders', [PurchaseOrderController::class, 'index']);
        Route::post('purchase-orders', [PurchaseOrderController::class, 'store']);
    });

    // Timesheet
    Route::prefix('timesheet')->group(function () {
        Route::post('clock-in', [TimesheetController::class, 'clockIn']);
        Route::post('clock-out', [TimesheetController::class, 'clockOut']);
        Route::get('me', [TimesheetController::class, 'me']);
    });

    // Telemetry
    Route::post('/telemetry/location', [TelemetryController::class, 'location']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    // Billing
    Route::prefix('billing')->middleware('role:OWNER,INTERNAL_MANAGER')->group(function () {
        Route::get('invoices', [InvoiceController::class, 'index']);
        Route::post('invoices', [InvoiceController::class, 'store']);
        Route::patch('invoices/{invoice}', [InvoiceController::class, 'update']);
        Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store']);
        Route::get('payments', [PaymentController::class, 'index']);
    });

    // Building Manager (BM)
    Route::prefix('bm')->middleware('role:BUILDING_MANAGER')->group(function () {
        Route::get('reports/monthly', [BmController::class, 'monthly']);
        Route::get('invoices', [BmController::class, 'invoices']);
        Route::post('invoices/{invoice}/pay', [BmController::class, 'pay']);
    });

    // Admin (System Admin only)
    Route::prefix('admin')->middleware(function($request, $next) {
        if (auth()->user()->system_role !== 'SYSTEM_ADMIN') {
            abort(403);
        }
        return $next($request);
    })->group(function () {
        Route::apiResource('users', UserAdminController::class);
        Route::apiResource('tenants', TenantAdminController::class);
        Route::get('tenants/{tenant}/stats', [TenantAdminController::class, 'stats']);
        Route::apiResource('plans', PlanController::class);
        Route::apiResource('billing/subscriptions', SubscriptionController::class)->parameters([
            'billing/subscriptions' => 'subscription'
        ]);
        Route::get('settings/{tenant}', [SettingController::class, 'show']);
        Route::patch('settings/{tenant}', [SettingController::class, 'update']);
        Route::get('dashboard/summary', [DashboardController::class, 'summary']);
        Route::get('reports/revenue', [ReportController::class, 'revenue']);
        Route::get('reports/expiring', [ReportController::class, 'expiring']);
        Route::get('logs', [LogController::class, 'index']);
    });
});
