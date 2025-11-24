<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileAuthController;
use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\FingerprintController;
use App\Models\Usuario; // Asegúrate de que este sea el modelo correcto de tu tabla de usuarios

// --- 1. Rutas Públicas (No requieren Login) ---
Route::post('/login', [MobileAuthController::class, 'login']);
Route::post('/validate-access', [AccessController::class, 'validateFingerprint']); 
Route::post('/fingerprint-enrolled', [FingerprintController::class, 'store']); 
Route::post('/fingerprint-error', [FingerprintController::class, 'store']);

// --- 2. Rutas Protegidas (Requieren Token de Acceso) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Rutas de Autenticación
    Route::get('/me', [MobileAuthController::class, 'me']);
    Route::post('/logout', [MobileAuthController::class, 'logout']);
    
    // RUTA CLAVE PARA LA TAREA: Marcar el Onboarding como completado
    Route::post('/onboarding-complete', [MobileAuthController::class, 'completeOnboarding']);
    
    // Otras rutas protegidas irían aquí (Ej: /rutinas, /ejercicios)
});


// --- 3. Ruta Ligera (Consulta de Estatus por ID) ---
// *Nota: Idealmente, esta información se obtendría en la ruta '/me' o en el login, 
// pero mantenemos tu ruta si es necesaria para el flujo IoT.
Route::get('/user-status/{id}', function ($id) {
    $usuario = Usuario::find($id);
    
    if (!$usuario) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }
    
    // Devolvemos lo que nos interesa: estatus y si tiene huella
    return response()->json([
        'estatus' => $usuario->estatus,
        'fingerprint_id' => $usuario->fingerprint_id
    ]);
});