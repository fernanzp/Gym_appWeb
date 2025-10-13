<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;

//Ruta base
Route::redirect('/', '/dashboard');

//Rutas para el formulario del login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/usuarios', 'usuarios')->name('usuarios');
    Route::view('/membresias', 'membresias')->name('membresias');
    Route::view('/accesos', 'accesos')->name('accesos');

    //Ruta para el registro de clientes
    Route::get('/clientes/crear', [ClienteController::class, 'create'])->name('clientRegister');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
});
