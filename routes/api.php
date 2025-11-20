<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileAuthController;
use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\FingerprintController;
use App\Models\Usuario;

Route::post('/login', [MobileAuthController::class, 'login']);
Route::post('/validate-access', [AccessController::class, 'validateFingerprint']); //Ruta para el acceso IoT
Route::post('/fingerprint-enrolled', [FingerprintController::class, 'store']); //Ruta para el modo de enrolamiento
Route::post('/fingerprint-error', [FingerprintController::class, 'store']);// Ruta para manejar los errores

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',     [MobileAuthController::class, 'me']);
    Route::post('/logout',[MobileAuthController::class, 'logout']);
});



// Ruta ligera para consultar el estado del usuario
Route::get('/user-status/{id}', function ($id) {
    $usuario = Usuario::find($id);
    
    // Devolvemos solo lo que nos interesa: estatus y si tiene huella
    return response()->json([
        'estatus' => $usuario->estatus,         // 0=Inicial, 8=Error, 9=Timeout
        'fingerprint_id' => $usuario->fingerprint_id // null o un número
    ]);
});