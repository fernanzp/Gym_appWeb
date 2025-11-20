<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivacionController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\PasswordResetController;

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
    Route::view('/membresias', 'membresias')->name('membresias');
    Route::get('/membresias', [MembresiaController::class, 'index'])->name('membresias');
    // Ruta para cambiar el estatus (Congelar/Reactivar)
    Route::put('/membresias/{id}/toggle-status', [MembresiaController::class, 'toggleStatus'])
        ->name('membresias.toggleStatus');
    Route::view('/accesos', 'accesos')->name('accesos');
});

// Rutas administrativas (Admin o Staff)
Route::middleware(['auth', 'can:admin-or-staff'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios');
    Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');   // Corregido: Apunta al Controller
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');     // Agregado: Necesario para el formulario
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    // Clientes
    Route::get('/clientes/crear', [ClienteController::class, 'create'])->name('clientRegister');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
});

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

Route::post('/cliente/retry-enroll/{userId}', [ClienteController::class, 'retryEnroll'])
     ->name('cliente.retry');

Route::post('/usuario/{id}/reset-fingerprint', [UsuarioController::class, 'resetFingerprint'])
    ->name('usuario.resetFingerprint');
