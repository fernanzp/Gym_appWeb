<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Membresia;

class AccessController extends Controller
{
    public function validateFingerprint(Request $request)
    {
        // Valida que el dispositivo IoT envió el 'fingerprint_id'
        $validatedData = $request->validate([
            'fingerprint_id' => 'required|integer',
        ]);

        // Busca al usuario que tenga asignado ese ID de huella
        $usuario = Usuario::where('fingerprint_id', $validatedData['fingerprint_id'])->first();

        // Si no se encuentra ningún usuario, deniega el acceso
        if (!$usuario) {
            return response()->json(['status' => 'denied', 'reason' => 'Fingerprint not registered']);
        }

        // Verifica si el usuario tiene una membresía activa y vigente
        $membresiaActiva = Membresia::where('usuario_id', $usuario->id)
                                    ->where('estatus', 1) // 1 = vigente
                                    ->where('fecha_fin', '>=', now()->toDateString())
                                    ->exists();

        if ($membresiaActiva) {
            // Si todo está en orden, concede el acceso
            return response()->json(['status' => 'allowed', 'user_name' => $usuario->nombre_comp]);
        } else {
            // Si la membresía está vencida o inactiva, deniega el acceso
            return response()->json(['status' => 'denied', 'reason' => 'Membership expired or inactive']);
        }
    }
}