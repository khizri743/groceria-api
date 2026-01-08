<?php

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

    // --- Admin Category Management ---
    // In a real app, you would add a middleware like 'is_admin' here
    Route::post('/categories', [ShopController::class, 'storeCategory']);
    Route::put('/categories/{id}', [ShopController::class, 'updateCategory']);
    Route::delete('/categories/{id}', [ShopController::class, 'destroyCategory']);

});

// --- Public Routes (Catalog, etc.) ---
// You will add Product/Category routes here later so guests can see items.
// Route::get('/products', ...);


// Categories Page
Route::get('/categories', [ShopController::class, 'categories']);
Route::get('/categories/{id}', [ShopController::class, 'showCategory']); // Get Single
Route::get('/search', [ShopController::class, 'search']);
Route::post('/products/recent', [ShopController::class, 'recent']); // POST because we send an array of IDs


// --- Authentication Routes (Breeze Defaults) ---
require __DIR__.'/auth.php';