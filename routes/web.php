<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductSupplierController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;

// ─── Auth Routes (Guest Only) ───────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Authenticated Routes ────────────────────────────────────────────────────
Route::middleware('auth.custom')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Products (CRUD + Search/Filter/Sort/Pagination)
    Route::resource('products', ProductController::class)->except(['show']);

    // Product-Supplier Many-to-Many
    Route::get('/products/{product}/suppliers', [ProductSupplierController::class, 'index'])->name('products.suppliers');
    Route::post('/products/{product}/suppliers/attach', [ProductSupplierController::class, 'attach'])->name('products.suppliers.attach');
    Route::post('/products/{product}/suppliers/detach', [ProductSupplierController::class, 'detach'])->name('products.suppliers.detach');

    // Categories (CRUD)
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Suppliers (CRUD)
    Route::resource('suppliers', SupplierController::class)->except(['show']);

    // Stock Transactions
    Route::get('/stock/in', [StockController::class, 'inForm'])->name('stock.in');
    Route::post('/stock/in', [StockController::class, 'storeIn'])->name('stock.in.store');
    Route::get('/stock/out', [StockController::class, 'outForm'])->name('stock.out');
    Route::post('/stock/out', [StockController::class, 'storeOut'])->name('stock.out.store');
    Route::get('/stock/history', [StockController::class, 'history'])->name('stock.history');

    // Users (Admin Only)
    Route::middleware('admin')->resource('users', UserController::class)->except(['show']);
});
