<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GymStatusController; 
use App\Http\Controllers\MobileAuthController;    
use App\Http\Controllers\Api\AccessController;    
use App\Http\Controllers\Api\FingerprintController; 
use App\Models\Usuario;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

// Login
Route::post('/login', [MobileAuthController::class, 'login']);

// Aforo
Route::get('/aforo', [GymStatusController::class, 'getAforo']);

// IoT / Huella
Route::post('/validate-access', [AccessController::class, 'validateFingerprint']); 
Route::post('/fingerprint-enrolled', [FingerprintController::class, 'store']); 
Route::post('/fingerprint-error', [FingerprintController::class, 'store']);

// Consulta ligera estatus
Route::get('/user-status/{id}', function ($id) {
    $usuario = Usuario::find($id);
    if (!$usuario) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }
    return response()->json([
        'estatus' => $usuario->estatus,
        'fingerprint_id' => $usuario->fingerprint_id
    ]);
});

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Gestión de Cuenta
    Route::get('/me', [MobileAuthController::class, 'me']);
    
    // Logout (Ahora apunta a MobileAuthController)
    Route::post('/logout', [MobileAuthController::class, 'logout']); 
    
    // Onboarding
    Route::post('/onboarding-complete', [MobileAuthController::class, 'completeOnboarding']);
    
    // Dashboard / Visuales
    Route::get('/header-data', [GymStatusController::class, 'getHeaderData']);
    Route::get('/user-profile', [GymStatusController::class, 'getUserProfile']);
    Route::get('/gym-stats', [GymStatusController::class, 'getBusynessStats']);
});