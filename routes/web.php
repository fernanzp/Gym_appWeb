<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;

//Ruta base
Route::get('/', function () {
    return view('welcome');
});

//Rutas para el formulario del login
Route::middleware('web')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
});

//Ruta para el dashboard
Route::view('/dashboard', 'dashboard')->name('dashboard');


//Ruta para el registro de clientes
Route::get('/clientes/crear', [ClienteController::class, 'create'])->name('clientRegister');
Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
