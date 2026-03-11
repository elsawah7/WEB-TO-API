<?php

use App\Http\Controllers\API\V1\Admin\CategoryController;
use App\Http\Controllers\API\V1\Admin\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    // Categories management
    Route::apiResource('categories', CategoryController::class);

    // Products management
    Route::resource('products', ProductController::class);
    Route::get('products/{product}/similar', [ProductController::class, 'similarProducts']);
    Route::post('products/images/upload', [ProductController::class, 'uploadImages'])->name('products.images.store');
    Route::delete('products/images/{image}/delete', [ProductController::class, 'deleteImage'])->name('products.images.destroy');
    Route::put('products/images/{image}/primary', [ProductController::class, 'setPrimary'])->name('products.images.primary');
});
