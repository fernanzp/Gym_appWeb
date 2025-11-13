<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivacionController;

//Ruta base
Route::get('/', function () {
    return view('welcome');
});

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

// Rutas para la activación de la cuenta
Route::get('/activar-cuenta', [ActivacionController::class, 'show'])
    ->name('activacion.show')
    ->middleware('guest'); // Solo invitados pueden ver esto

Route::get('/activacion-exitosa', function () {
    return view('activationSuccessful'); // coincide con resources/views/activate.blade.php
})->name('activationSuccessful');

Route::post('/activar-cuenta', [ActivacionController::class, 'store'])
    ->name('activacion.store')
    ->middleware('guest');

