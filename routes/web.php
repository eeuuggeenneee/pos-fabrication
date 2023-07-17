<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect('/admin');
});

Auth::routes();

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
    Route::resource('products', ProductController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('orders', OrderController::class);
    Route::resource('discounts', DiscountController::class);

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::post('/cart/change-qty', [CartController::class, 'changeQty']);
    Route::delete('/cart/delete', [CartController::class, 'delete']);
    Route::delete('/cart/empty', [CartController::class, 'empty']);

    Route::get('/discounts/create', [DiscountController::class, 'create'])->name('discounts.create');
    Route::get('/discounts/{discount}/edit', [DiscountController::class, 'edit'])->name('discounts.edit');
    Route::put('/discounts/{discount}', [DiscountController::class, 'update'])->name('discounts.update');
    Route::post('/discounts', [DiscountController::class, 'store'])->name('discounts.store');
    Route::get('/discounts', [DiscountController::class, 'index'])->name('discounts.index');
    Route::get('/discounts/promocode/{promocode}', [DiscountController::class, 'show'])->name('discounts.promocode');
    Route::get('/discounts/promocode', [DiscountController::class, 'promocode'])->name('discounts.promocode2');

    Route::get('/reportForm', function () {
        return view('report.report');
    })->name('reportForm');
    
    Route::post('/generateReport', [ReportController::class, 'generateReport'])->name('generateReport');

});
