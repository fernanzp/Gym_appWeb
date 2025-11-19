<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileAuthController;
use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\FingerprintController;

Route::post('/login', [MobileAuthController::class, 'login']);
Route::post('/validate-access', [AccessController::class, 'validateFingerprint']); //Ruta para el acceso IoT
Route::post('/fingerprint-enrolled', [FingerprintController::class, 'store']); //Ruta para el modo de enrolamiento
Route::post('/fingerprint-error', [FingerprintController::class, 'store']);// Ruta para manejar los errores

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',     [MobileAuthController::class, 'me']);
    Route::post('/logout',[MobileAuthController::class, 'logout']);
});

