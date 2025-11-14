<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;
use Carbon\Carbon;

class ActivacionController extends Controller
{
    /**
     * Muestra el formulario para crear la contraseña.
     */
    public function show(Request $request)
    {
        // Validamos que el token y el email vengan en la URL
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
        ]);

        $tokenData = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        // Validar que el token exista y no haya expirado (ej. 60 minutos)
        if (!$tokenData || Carbon::parse($tokenData->created_at)->addMinutes(60)->isPast()) {
            return redirect()->route('login')->withErrors(['general' => 'El enlace de activación no es válido o ha expirado.']);
        }

        // Pasamos el token y el email a la vista (ocultos)
        return view('acountActivation', [
            'token' => $request->token,
            'email' => $request->email
        ]);
    }

    /**
     * Almacena la nueva contraseña y activa al usuario.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email'      => 'required|email|exists:usuarios,email',
            'token'      => 'required|string',
            'contrasena' => 'required|confirmed|min:8', // 'confirmed' busca 'contrasena_confirmation'
        ]);

        // Validamos el token otra vez
        $tokenData = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$tokenData) {
            return back()->withInput()->withErrors(['general' => 'El enlace de activación no es válido.']);
        }

        // Buscamos al usuario
        $usuario = Usuario::where('email', $request->email)->firstOrFail();

        // Actualizamos al usuario
        $usuario->contrasena = $request->contrasena; // El mutator en Usuario.php se encarga de hashear
        $usuario->estatus = 1; // 1 = activo
        $usuario->save();

        // Eliminamos el token para que no se reutilice
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('activacion.exitosa')->with('success', '¡Tu cuenta ha sido activada y tu contraseña establecida con éxito!');
    }
}