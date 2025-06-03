<?php
//api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController; 
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Cashier\InvoiceController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\AdminOrderController;

Route::middleware(['auth', 'role:admin'])->group(function () {
    // Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Route::get('/brands', [BrandController::class, 'getBrands']);
    Route::post('/brands', [BrandController::class, 'store']);
    Route::put('/brands/{id}', [BrandController::class, 'update']);
    Route::delete('/brands/{id}', [BrandController::class, 'destroy']);
    
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    Route::get('/users', [UsersController::class, 'index']);
    Route::post('/users', [UsersController::class, 'store']);
    Route::put('/users/{id}/role', [UsersController::class, 'updateRole']);
    Route::delete('/users/{id}', [UsersController::class, 'destroy']);

    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);

    Route::get('/admin/orders', [AdminOrderController::class, 'index']);
    // Xem chi tiết đơn hàng
    Route::get('/admin/orders/{id}', [AdminOrderController::class, 'show']);

    // Cập nhật trạng thái đơn hàng
    Route::put('/admin/orders/{id}', [AdminOrderController::class, 'update']);

    // Xóa đơn hàng
    Route::delete('/admin/orders/{id}', [AdminOrderController::class, 'destroy']);
 
});


Route::get('/products', [ProductController::class, 'index']);
Route::get('/brands', [BrandController::class, 'getBrands']);
Route::get('/categories', [CategoryController::class, 'index']);

Route::middleware('auth')->group(function () {
    
    // Route::get('/products', [ProductController::class, 'index']);

    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
 
});
Route::patch('/products/{id}/update-stock', [ProductController::class, 'updateStock']);

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::put('/cart/update/{id}', [CartController::class, 'updateCart']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart']);
    
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'placeOrder']);
    Route::get('/orders/{id}', [OrderController::class, 'show']); // Lấy chi tiết đơn hàng
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);

    Route::get('/user', [UserController::class, 'getUserInfo']);
});
