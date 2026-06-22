<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;


// auth
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::get('me', [AuthController::class, 'me'])
        ->middleware('auth:sanctum');
});


//categorie
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/types', [CategoryController::class, 'types']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);



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





