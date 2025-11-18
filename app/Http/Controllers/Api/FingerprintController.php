<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;

class FingerprintController extends Controller
{
    public function store(Request $request)
    {
        try {
            // El webhook de Particle enviará el JSON crudo en el cuerpo
            $data = json_decode($request->getContent(), true);

            $userId = $data['user_id'] ?? null;
            $slot   = $data['slot'] ?? null;

            if (!$userId || !$slot) {
                Log::warning('Datos inválidos recibidos del Photon', ['payload' => $data]);
                return response()->json(['error' => 'Datos inválidos'], 400);
            }

            $usuario = Usuario::find($userId);
            if (!$usuario) {
                Log::warning('Usuario no encontrado para huella', ['user_id' => $userId]);
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            // Guardamos el slot (ID de la huella)
            $usuario->fingerprint_id = $slot;
            $usuario->save();

            Log::info("Huella registrada exitosamente para usuario #{$userId}, slot #{$slot}");

            return response()->json([
                'success' => true,
                'message' => 'Huella registrada exitosamente.',
                'user_id' => $userId,
                'slot' => $slot,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al registrar huella: '.$e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}