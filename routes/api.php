<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TypeCategorieController;
use App\Http\Controllers\WishlistController;


// auth
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::get('me', [AuthController::class, 'me'])
        ->middleware('auth:sanctum');

    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');
});
// Wishlist (favoris)
Route::get('/wishlist/ads', [WishlistController::class, 'index']);
Route::post('/wishlist/ads/{adId}', [WishlistController::class, 'add']);
Route::delete('/wishlist/ads/{adId}', [WishlistController::class, 'remove']);
Route::get('/wishlist/check', [WishlistController::class, 'check']);
Route::get('/test', function () {
    return ['message' => 'API is working'];
});
//categorie
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/all', [CategoryController::class, 'all']);
    Route::get('/types', [CategoryController::class, 'types']);
    Route::get('/{id}', [CategoryController::class, 'show']);

    Route::post('/', [CategoryController::class, 'store']);
    Route::put('/{id}', [CategoryController::class, 'update']);
    Route::patch('/{id}/toggle', [CategoryController::class, 'toggle']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
});
// annonces - routes complémentaires
Route::put('/annonces/{id}', [AdController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/annonces/{id}', [AdController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('/annonces/{id}/view', [AdController::class, 'incrementView']);
Route::get('/annonces/mine', [AdController::class, 'mine'])->middleware('auth:sanctum');


// type cat 
// Route::get('/typecategories', function () {
//     return \App\Models\TypeCategorie::all();
// });

Route::prefix('typecategories')->group(function () {

    Route::get('/', [TypeCategorieController::class, 'index']);
    Route::get('/all', [TypeCategorieController::class, 'all']);
    Route::get('/{id}', [TypeCategorieController::class, 'show']);

    Route::post('/', [TypeCategorieController::class, 'store']);
    Route::put('/{id}', [TypeCategorieController::class, 'update']);

    Route::patch('/{id}/toggle', [TypeCategorieController::class, 'toggle']);
    Route::delete('/{id}', [TypeCategorieController::class, 'destroy']);
});




//annonce
Route::get('/annonces', [AdController::class, 'index']);
Route::get('/annonces/{id}', [AdController::class, 'show']);
Route::post('/annonces', [AdController::class, 'store'])->middleware('auth:sanctum');
Route::post('/annonces/{id}/like', [AdController::class, 'like'])->middleware('auth:sanctum');
Route::post('/annonces/{id}/comments', [AdController::class, 'comment'])->middleware('auth:sanctum');

//product
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store'])->middleware('auth:sanctum');

//order
Route::get('/orders', [OrderController::class, 'index'])->middleware('auth:sanctum');
Route::post('/orders', [OrderController::class, 'store'])->middleware('auth:sanctum');
Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->middleware('auth:sanctum');





