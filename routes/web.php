<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivacionController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\RecepcionistaController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ReporteController;

// Ruta base
Route::get('/', function () {
    return view('welcome');
});

// Autenticación (Login)
Route::middleware('web')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
});

// Rutas protegidas generales (Cualquier usuario logueado)
Route::middleware('auth')->group(function () {
    // 🔴 RUTA DE LOGOUT (Nueva)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::view('/membresias', 'membresias')->name('membresias');
    Route::get('/membresias', [MembresiaController::class, 'index'])->name('membresias');

    // 🔥 RUTAS DE RENOVACIÓN (Agregadas del segundo documento)
    // Paso 1: Del Modal a la Vista de Pago
    Route::post('/membresias/renovar/preparar', [MembresiaController::class, 'prepararRenovacion'])
        ->name('membresias.prepararRenovacion');

    // Paso 2: De la Vista de Pago a la Base de Datos
    Route::post('/membresias/renovar/procesar', [MembresiaController::class, 'procesarRenovacion'])
        ->name('membresias.procesarRenovacion');

    // Ruta para cambiar el estatus (Congelar/Reactivar)
    Route::put('/membresias/{id}/toggle-status', [MembresiaController::class, 'toggleStatus'])
        ->name('membresias.toggleStatus');

    Route::view('/entradas-salidas', 'entradasSalidas')->name('entradas-salidas');
});

// Rutas administrativas (Admin o Staff)
Route::middleware(['auth', 'can:admin-or-staff'])->group(function () {

    // Ruta para establecer el aforo maximo
    Route::post('/configuracion/aforo', [DashboardController::class, 'updateAforo'])->name('configuracion.updateAforo');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Ruta para guardar planes
    // Ruta para CREAR (POST)
    Route::post('/planes', [PlanController::class, 'store'])->name('planes.store');

    // Ruta para EDITAR (PUT)
    Route::put('/planes/{plan}', [PlanController::class, 'update'])->name('planes.update');

    // Ruta para ELIMINAR (DELETE) - ¡Esta es la que te falta o no detecta!
    Route::delete('/planes/{plan}', [PlanController::class, 'destroy'])->name('planes.destroy');

    // Análisis y Reportes (Vista de Diseño)
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/api/analytics/revenue', [AnalyticsController::class, 'revenueData'])
        ->name('api.analytics.revenue');
    Route::get('/api/analytics/live', [AnalyticsController::class, 'liveActivityData'])
        ->name('api.analytics.live');

    //Pdf
    Route::get('/reportes/descargar-analytics', [ReporteController::class, 'descargarAnalytics'])
        ->name('reportes.analytics');

    // Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios');
    Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit'); 
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update'); 
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    //Ruta para registrar una nueva recepcionista
    Route::get('/recepcionista/registrar', [RecepcionistaController::class, 'create'])->name('receptionist.create');
    Route::post('/recepcionista/registrar', [RecepcionistaController::class, 'store'])->name('receptionist.store');

    // Clientes
    Route::get('/clientes/crear', [ClienteController::class, 'create'])->name('clientRegister');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');

    // Ruta para mostrar la vista de pago tras el registro
    Route::get('/usuarios/{id}/pago-inicial', [ClienteController::class, 'vistaPagoRegistro'])
        ->name('clientes.pagoInicial');

    // Ruta para finalizar el proceso (botón "Pago Recibido")
    Route::get('/usuarios/{id}/finalizar-registro', [ClienteController::class, 'finalizarRegistro'])
        ->name('clientes.finalizarRegistro');

    // Ruta para cancelar el registro (eliminar usuario recién creado)
    Route::delete('/usuarios/{id}/cancelar-registro', [ClienteController::class, 'cancelarRegistro'])
        ->name('clientes.cancelarRegistro');

    // Pagos (Vista Mockup)
    Route::get('/pago-membresia', function () {
        return view('payment');
    })->name('pagos.show');

    // Visitas
    Route::post('/access/visita', [AccessController::class, 'registrarVisitaManual'])
         ->name('access.visita');

    // Gestión Biométrica
    Route::post('/cliente/retry-enroll/{userId}', [ClienteController::class, 'retryEnroll'])
         ->name('cliente.retry');

    Route::post('/usuario/{id}/reset-fingerprint', [UsuarioController::class, 'resetFingerprint'])
        ->name('usuario.resetFingerprint');
});

// 🔥 RUTAS API MANUALES (Definidas aquí para asegurar que funcionen en el hosting)
// -----------------------------------------------------------------------------

// 1. Ruta para el Aforo en Vivo (Dashboard)
Route::get('/api/aforo-live', [DashboardController::class, 'getAforoEnVivo']);

// 2. Ruta para el estado del modal de huella (EditUser / ClientRegister)
use App\Models\Usuario;

// -----------------------------------------------------------------------------

// Rutas de activación (Solo invitados)
Route::middleware('guest')->group(function () {
    Route::get('/activar-cuenta', [ActivacionController::class, 'show'])->name('activacion.show');
    Route::post('/activar-cuenta', [ActivacionController::class, 'store'])->name('activacion.store');

    Route::get('/olvide-contrasena', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/olvide-contrasena', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/restablecer-contrasena', [PasswordResetController::class, 'reset'])->name('password.update');

    Route::view('/contrasena-restablecida', 'auth.passwordResetSuccess')->name('password.success');
    Route::get('/activacion-exitosa', function () {
        return view('activationSuccessful');
    })->name('activacion.exitosa');
});

// 🔥 RUTA DE EMERGENCIA PARA LIMPIAR CACHÉ (Úsala una vez si sigue fallando)
Route::get('/clear-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    return 'Rutas limpiadas';
});