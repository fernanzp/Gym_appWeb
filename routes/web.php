<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DashboardController;

//Ruta base
Route::get('/', function () {
    return view('welcome');
});

// Ruta pública para activar cuenta
Route::get('/activar-cuenta', function () {
    return view('acountActivation');
})->name('activarCuenta');

//Rutas para el formulario del login
Route::middleware('web')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
});
Route::middleware('auth')->group(function () {
    Route::view('/membresias', 'membresias')->name('membresias');
    Route::view('/accesos', 'accesos')->name('accesos');
});

Route::middleware(['auth','can:admin-or-staff'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios');
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    Route::get('/clientes/crear', [ClienteController::class, 'create'])->name('clientRegister');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
});

