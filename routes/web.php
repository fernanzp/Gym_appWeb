<?php

use Illuminate\Support\Facades\Route;

//Ruta base
Route::get('/', function () {
    return view('welcome');
});

//Ruta para el dashboard
Route::view('/dashboard', 'dashboard');

//Ruta para el login
Route::view('/login', 'login');

//Ruta para el registro de clientes
Route::view('/register', 'clientRegister');