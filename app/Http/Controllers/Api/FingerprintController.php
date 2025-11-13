<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;

class FingerprintController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'fingerprint_id' => 'required|integer'
        ]);

        $usuario = Usuario::find($validated['user_id']);

        if ($usuario) {
            $usuario->fingerprint_id = $validated['fingerprint_id'];
            $usuario->save();

            return response()->json(['message' => 'Huella registrada correctamente']);
        }

        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }
}
