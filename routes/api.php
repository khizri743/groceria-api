<?php

use App\Http\Controllers\Api\Auth\PinController;
use App\Http\Controllers\Api\Content\ArticleController;
use App\Http\Controllers\Api\Shop\CartController;
use App\Http\Controllers\Api\Shop\ChatController;
use App\Http\Controllers\Api\Shop\OrderController;
use App\Http\Controllers\Api\Shop\ShopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import your new Controllers
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\Api\User\AddressController;
use App\Http\Controllers\Api\User\FavoriteController;
use App\Http\Controllers\Api\User\PaymentMethodController;
use App\Http\Controllers\Api\User\WalletController;
use App\Http\Controllers\Auth\PasswordResetLinkController;

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


// ADMIN ROUTES
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

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest:sanctum')
    ->name('password.email');

// --- Protected Routes ---
Route::middleware(['auth:sanctum'])->group(function () {

    // 1. Default User Data
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // 2. Profile & Settings
    Route::get('/profile', [ProfileController::class, 'show']);          
    Route::post('/profile/update', [ProfileController::class, 'update']);   
    Route::post('/settings', [ProfileController::class, 'updateSettings']);

    // 3. Address Book
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);

    // 4. Favorites / Wishlist 
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);

    // Payment Methods 
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::post('/payment-methods', [PaymentMethodController::class, 'store']);

     // Wallet
    Route::get('/wallet', [WalletController::class, 'show']);
    Route::post('/wallet/top-up', [WalletController::class, 'topUp']);

    // Verify PIN
    Route::post('/auth/verify-pin', [PinController::class, 'verify']);

    Route::get('/orders', [OrderController::class, 'index']); 

    Route::post('/orders/{id}/tip', [OrderController::class, 'addTip']);
    Route::post('/orders/{id}/review', [OrderController::class, 'submitReview']);

    // CHAT
    Route::get('/orders/{id}/chat', [ChatController::class, 'index']); 
    Route::post('/orders/{id}/chat', [ChatController::class, 'store']); 
});

// --- Public Routes --- //


// Categories Page
Route::get('/categories', [ShopController::class, 'categories']);
Route::get('/categories/{id}', [ShopController::class, 'showCategory']); 
Route::get('/search', [ShopController::class, 'search']);


// Product Routes
Route::get('/products/{id}', [ShopController::class, 'showProduct']);
Route::post('/products/compare', [ShopController::class, 'compare']);
Route::post('/products/recent', [ShopController::class, 'recent']); 
// Categories Page
Route::get('/categories', [ShopController::class, 'categories']);
Route::get('/categories/{id}', [ShopController::class, 'showCategory']);
Route::get('/categories/{id}/products', [ShopController::class, 'getCategoryProducts']);
Route::get('/search', [ShopController::class, 'search']);

// Product Routes
Route::get('/products', [ShopController::class, 'products']); 
Route::get('/products/{id}', [ShopController::class, 'showProduct']);
Route::post('/products/compare', [ShopController::class, 'compare']);
Route::post('/products/recent', [ShopController::class, 'recent']);

// CART
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart/add', [CartController::class, 'addToCart']);
Route::delete('/cart/{itemId}', [CartController::class, 'removeItem']);

// CHECKOUT SUMMARY
Route::post('/checkout/summary', [CartController::class, 'checkoutSummary']);

Route::post('/orders/place', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);

// ARTICLES
Route::get('/articles', [ArticleController::class, 'index']); 
Route::get('/articles/{id}', [ArticleController::class, 'show']); 



// --- Authentication Routes ---
require __DIR__.'/auth.php';