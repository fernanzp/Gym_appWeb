<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileAuthController;
use App\Http\Controllers\Api\AccessController;

Route::post('/login', [MobileAuthController::class, 'login']);
Route::post('/validate-access', [AccessController::class, 'validateFingerprint']); //Ruta para el acceso IoT

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',     [MobileAuthController::class, 'me']);
    Route::post('/logout',[MobileAuthController::class, 'logout']);
});

