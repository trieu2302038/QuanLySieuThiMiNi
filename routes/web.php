<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Cashier\CashierController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Cashier\InvoiceController;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\User\OrderController;


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/cashier/invoices', function () {
        return view('cashier.invoices');
    })->name('cashier.invoices');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/cashier/dashboard', [CashierController::class, 'index'])->name('cashier.dashboard');
    // Route::get('/user/dashboard', [UserController::class, 'index'])->name('user.dashboard');
});

Route::get('/user/dashboard', [UserController::class, 'index'])->name('user.dashboard');
Route::get('/', [UserController::class, 'index']);

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/categories', function () {
        return view('admin.categories');
    })->name('admin.categories');

    Route::get('/admin/brands', function () {
        return view('admin.brands');
    })->name('admin.brands');

    Route::get('/admin/products', function () {
        return view('admin.products');
    })->name('admin.products');

    Route::get('/admin/users', function () {
        return view('admin.users');
    })->name('admin.users');

    Route::get('/admin/orders', function () {
        return view('admin.orders');
    })->name('admin.orders');

    Route::get('/admin/orders/{id}', function () {
        return view('admin.order-detail');
    });

    // Trang chi tiết đơn hàng
    Route::get('/admin/orders/{id}', function ($id) {
        return view('admin.order-detail', ['id' => $id]);
    })->name('admin.order-detail');

});

Route::middleware(['auth', 'role:cashier'])->group(function () {
    Route::get('/cashier/dashboard', [CashierController::class, 'index'])->name('cashier.dashboard');
});

Route::get('/storage/{path}', function ($path) {
    return response()->file(storage_path('app/' . $path));
})->where('path', '.*');


Route::middleware(['auth','role:user'])->group(function () {
    Route::get('/orders', [OrderController::class, 'ordersPage'])->name('user.orders');
    Route::get('/orders/{id}', [OrderController::class, 'orderDetailPage'])->name('user.order-detail');
    Route::get('/cart', function () {
        return view('user.cart');
    })->name('user.cart');
});


