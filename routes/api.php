<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileAuthController;

Route::post('/login', [MobileAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',     [MobileAuthController::class, 'me']);
    Route::post('/logout',[MobileAuthController::class, 'logout']);
});

