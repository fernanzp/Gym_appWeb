<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MobileAuthController extends Controller
{
    // POST /api/login  -> devuelve { token, user }
    public function login(Request $r)
    {
        $data = $r->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $u = Usuario::where('email', $data['email'])->first();
        if (!$u || !Hash::check($data['password'], $u->contrasena)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $token = $u->createToken('mobile')->plainTextToken;

        return [
            'token' => $token,
            'user'  => [
                'id'          => $u->id,
                'nombre_comp' => $u->nombre_comp,
                'email'       => $u->email,
            ],
        ];
    }

    // GET /api/me (protegida con auth:sanctum)
    public function me(Request $r)
    {
        return $r->user();
    }

    // POST /api/logout (protegida con auth:sanctum)
    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()->delete();
        return ['ok' => true];
    }
}
