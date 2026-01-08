<?php

use App\Http\Controllers\Api\Shop\CartController;
use App\Http\Controllers\Api\Shop\ShopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import your new Controllers
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\Api\User\AddressController;
use App\Http\Controllers\Api\User\FavoriteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// ADMIN ROUTES (Only role='admin')
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    
    // Create, Update, Delete Categories
    Route::post('/categories', [ShopController::class, 'storeCategory']);
    Route::put('/categories/{id}', [ShopController::class, 'updateCategory']);
    Route::delete('/categories/{id}', [ShopController::class, 'destroyCategory']);

    // Product Management
    Route::post('/products', [ShopController::class, 'storeProduct']);
    Route::put('/products/{id}', [ShopController::class, 'updateProduct']);
    Route::delete('/products/{id}', [ShopController::class, 'destroyProduct']);

});

// --- Protected Routes (User must be logged in) ---
Route::middleware(['auth:sanctum'])->group(function () {

    // 1. Default User Data (Basic)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // 2. Profile & Settings (Screen: Profile, Edit Profile, Settings)
    Route::get('/profile', [ProfileController::class, 'show']);             // Get details + settings
    Route::post('/profile/update', [ProfileController::class, 'update']);   // Upload Avatar / Change Name
    Route::post('/settings', [ProfileController::class, 'updateSettings']); // Toggle Dark Mode / Notifications

    // 3. Address Book (Screen: Addresses, Add Address)
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);

    // 4. Favorites / Wishlist (Screen: My Favorites)
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);

});

// --- Public Routes --- //


// Categories Page
Route::get('/categories', [ShopController::class, 'categories']);
Route::get('/categories/{id}', [ShopController::class, 'showCategory']); // Get Single
Route::get('/search', [ShopController::class, 'search']);


// Product Routes
Route::get('/products/{id}', [ShopController::class, 'showProduct']);
Route::post('/products/compare', [ShopController::class, 'compare']);
Route::post('/products/recent', [ShopController::class, 'recent']); // POST because we send an array of IDs
// Categories Page
Route::get('/categories', [ShopController::class, 'categories']);
Route::get('/categories/{id}', [ShopController::class, 'showCategory']);
Route::get('/categories/{id}/products', [ShopController::class, 'getCategoryProducts']); // Don't forget this one we made earlier!
Route::get('/search', [ShopController::class, 'search']);

// Product Routes
Route::get('/products', [ShopController::class, 'products']); // <--- ADD THIS LINE
Route::get('/products/{id}', [ShopController::class, 'showProduct']);
Route::post('/products/compare', [ShopController::class, 'compare']);
Route::post('/products/recent', [ShopController::class, 'recent']);

// CART (Accessible by Guest OR User)
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart/add', [CartController::class, 'addToCart']);
Route::delete('/cart/{itemId}', [CartController::class, 'removeItem']);

// CHECKOUT SUMMARY
Route::post('/checkout/summary', [CartController::class, 'checkoutSummary']);

// --- Authentication Routes (Breeze Defaults) ---
require __DIR__.'/auth.php';