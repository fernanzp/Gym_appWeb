<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Usuario; // ¡Asegúrate de que tu modelo se llame así!
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class MobileAuthController extends Controller
{
    // POST /api/login
    public function login(Request $r)
    {
        $data = $r->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Verificar si el usuario existe
        $u = Usuario::where('email', $data['email'])->first();

        // Verificar contraseña (asumiendo que en DB se llama 'contrasena')
        if (!$u || !Hash::check($data['password'], $u->contrasena)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        // Crear Token
        $token = $u->createToken('mobile')->plainTextToken;

        // --- DEVOLVER DATOS + ESTATUS ---
        return [
            'token' => $token,
            'user'  => [
                'id'          => $u->id,
                'nombre_comp' => $u->nombre_comp,
                'email'       => $u->email,
                'estatus'     => $u->estatus, // IMPORTANTE: Enviar el estatus para Flutter
            ],
        ];
    }

    // POST /api/onboarding-complete (Protegida)
    public function completeOnboarding(Request $request)
    {
        // Obtener el ID del usuario logueado
        $authId = Auth::id();

        if (!$authId) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        // Buscar al usuario
        $user = Usuario::find($authId);

        // Si el usuario es nuevo (Estatus 0), lo actualizamos a 1
        if ($user && $user->estatus == 0) {
            $user->estatus = 1;
            $user->save();
            
            return response()->json(['message' => 'Bienvenida completada. Estatus actualizado.']);
        }

        return response()->json(['message' => 'El usuario ya estaba activo.'], 200);
    }

    // GET /api/me
    public function me(Request $r)
    {
        return $r->user();
    }

    // POST /api/logout
    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()->delete();
        return ['ok' => true];
    }
}
