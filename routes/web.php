<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\ProductListingController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

/*
|--------------------------------------------------------------------------
| Storefront Routes (Public)
|--------------------------------------------------------------------------
*/

Route::get('/', [ProductListingController::class, 'index'])->name('home');

Route::get('/products/{product}', [ProductListingController::class, 'show'])->name('products.show');

/*
|--------------------------------------------------------------------------
| Authenticated Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    // Cart API
    Route::get('/api/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/api/cart', [CartController::class, 'store'])->name('cart.store');
    Route::put('/api/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/api/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    // Checkout
    Route::post('/api/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Order History
    Route::get('/orders', [OrderHistoryController::class, 'index'])->name('orders.index');
});

/*
|--------------------------------------------------------------------------
| Admin CMS Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Products Management
    Route::get('/products', [Admin\ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [Admin\ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [Admin\ProductController::class, 'destroy'])->name('products.destroy');

    // Orders Management
    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::delete('/orders/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');

    // Users Management
    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/toggle-status', [Admin\UserController::class, 'toggleStatus'])->name('users.toggleStatus');

    // Activity Logs
    Route::get('/activity-logs', [Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
});

// require __DIR__.'/settings.php';
