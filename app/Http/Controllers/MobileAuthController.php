<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        // Verificar contraseña
        if (!$u || !Hash::check($data['password'], $u->contrasena)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        // Crear Token
        $token = $u->createToken('mobile')->plainTextToken;

        // Devolver Token y datos básicos (incluyendo estatus para onboarding)
        return [
            'token' => $token,
            'user'  => [
                'id'          => $u->id,
                'nombre_comp' => $u->nombre_comp,
                'email'       => $u->email,
                'estatus'     => $u->estatus, 
            ],
        ];
    }

    // POST /api/onboarding-complete
    public function completeOnboarding(Request $request)
    {
        // Obtenemos el usuario directamente del request (gracias al token)
        $user = $request->user();

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
        // Borra el token actual para cerrar la sesión en el dispositivo
        $r->user()->currentAccessToken()->delete();
        
        return response()->json([
            'status' => 'success', 
            'message' => 'Sesión cerrada correctamente'
        ]);
    }
}