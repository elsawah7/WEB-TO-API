<?php

use App\Http\Controllers\API\V1\Admin\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    // Categories management
    Route::apiResource('categories', CategoryController::class);
});
