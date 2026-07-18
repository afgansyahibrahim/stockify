<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StockMutationController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\ActivityLogController;


/*
|--------------------------------------------------------------------------
| Guest
|--------------------------------------------------------------------------
| Hanya dapat dibuka sebelum login.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.process');
});


/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Semua user yang sudah login
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Admin, Manager, dan Staff
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager,staff')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/stock-in', [StockMutationController::class, 'stockIn'])
            ->name('stock.in');

        Route::post('/stock-in', [StockMutationController::class, 'storeIn'])
            ->name('stock.in.store');

        Route::get('/stock-out', [StockMutationController::class, 'stockOut'])
            ->name('stock.out');

        Route::post('/stock-out', [StockMutationController::class, 'storeOut'])
            ->name('stock.out.store');

        // Riwayat pengajuan milik user sendiri
        Route::get('/my-submissions', [StockMutationController::class, 'myHistory'])
            ->name('stock.my-history');

        // Stock Opname: Staff boleh membuat dan melihat miliknya sendiri
        Route::get(
            '/stock-opnames/products/search',
            [StockOpnameController::class, 'searchProducts']
            )->name('stock-opnames.products.search');

        Route::get('/stock-opnames', [StockOpnameController::class, 'index'])
            ->name('stock-opnames.index');

        Route::get('/stock-opnames/create', [StockOpnameController::class, 'create'])
            ->name('stock-opnames.create');

        Route::post('/stock-opnames', [StockOpnameController::class, 'store'])
            ->name('stock-opnames.store');

        Route::delete('/stock-opnames/{stockOpname}', [StockOpnameController::class, 'destroy'])
            ->name('stock-opnames.destroy');
        
        Route::get(
            '/products',
            [ProductController::class, 'index']
        )->name('products.index');
        Route::get('/suppliers', [SupplierController::class, 'index'])
            ->name('suppliers.index');
        Route::get(
            '/mutation-history',
            [StockMutationController::class, 'history']
        )->name('stock.history');
    });


    /*
    |--------------------------------------------------------------------------
    | Admin dan Manager
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager')->group(function () {
        Route::resource('products', ProductController::class)
            ->only(['create', 'store', 'update', 'destroy']);

        Route::resource('categories', CategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('suppliers', SupplierController::class)
            ->only(['store', 'update', 'destroy']);

        Route::get('/approvals', [ApprovalController::class, 'index'])
            ->name('approvals.index');

        Route::post('/approvals/{transaction}/approve', [ApprovalController::class, 'approve'])
            ->name('approvals.approve');

        Route::post('/approvals/{transaction}/reject', [ApprovalController::class, 'reject'])
            ->name('approvals.reject');

        // Persetujuan Stock Opname
        Route::post('/stock-opnames/{stockOpname}/approve', [StockOpnameController::class, 'approve'])
            ->name('stock-opnames.approve');

        Route::post('/stock-opnames/{stockOpname}/reject', [StockOpnameController::class, 'reject'])
            ->name('stock-opnames.reject');
        Route::patch(
            '/products/{product}/activate',
            [ProductController::class, 'activate']
        )->name('products.activate');
    });


    /*
    |--------------------------------------------------------------------------
    | Admin saja
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])
            ->name('reports.export.pdf');

        Route::resource('users', UserController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::get(
            '/activity-logs',
            [ActivityLogController::class, 'index']
        )->name('activity-logs.index');
    });
});