<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\VendorRegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/vendor/register', [VendorRegisterController::class, 'register']);
});

// Public product routes
Route::prefix('categories')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\CategoryController::class, 'index']);
    Route::get('/parents', [App\Http\Controllers\Api\CategoryController::class, 'parents']);
    Route::get('/{slug}', [App\Http\Controllers\Api\CategoryController::class, 'show']);
});

Route::prefix('brands')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\BrandController::class, 'index']);
    Route::get('/{slug}', [App\Http\Controllers\Api\BrandController::class, 'show']);
});

Route::prefix('products')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\ProductController::class, 'index']);
    Route::get('/featured', [App\Http\Controllers\Api\ProductController::class, 'featured']);
    Route::get('/on-sale', [App\Http\Controllers\Api\ProductController::class, 'onSale']);
    Route::get('/{slug}', [App\Http\Controllers\Api\ProductController::class, 'show']);
    Route::get('/{productId}/reviews', [App\Http\Controllers\Api\ReviewController::class, 'index']);
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [LogoutController::class, 'logout']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/change-password', [ProfileController::class, 'changePassword']);
    });

    // Reviews (authenticated users)
    Route::post('/products/{productId}/reviews', [App\Http\Controllers\Api\ReviewController::class, 'store']);

    // Wishlist
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\WishlistController::class, 'index']);
        Route::post('/{productId}', [App\Http\Controllers\Api\WishlistController::class, 'store']);
        Route::delete('/{productId}', [App\Http\Controllers\Api\WishlistController::class, 'destroy']);
        Route::get('/check/{productId}', [App\Http\Controllers\Api\WishlistController::class, 'check']);
    });

    // Vendor product management
    Route::prefix('vendor/products')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\Vendor\VendorProductController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\Vendor\VendorProductController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\Vendor\VendorProductController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\Vendor\VendorProductController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\Vendor\VendorProductController::class, 'destroy']);
    });

    // Test route
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
