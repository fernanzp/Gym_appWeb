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
            $data = json_decode($request->getContent(), true);

            $userId = $data['user_id'] ?? null;
            $slot   = $data['slot'] ?? null;
            $status = $data['status'] ?? 'unknown'; // <-- CAPTURAR EL STATUS

            if (!$userId || $status === 'unknown') {
                Log::warning('Datos inválidos recibidos del Photon (Falta user_id o status)', ['payload' => $data]);
                return response()->json(['error' => 'Datos inválidos'], 400);
            }

            $usuario = Usuario::find($userId);
            if (!$usuario) {
                Log::warning('Usuario no encontrado para huella', ['user_id' => $userId]);
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            // --- LÓGICA DE MANEJO DE ESTADOS ---
            if ($status === 'success') {
                // 1. REGISTRO EXITOSO: El Photon lo guardó y nos dio el slot
                if (!$slot) {
                    Log::error('Status es Success pero falta Slot.', ['payload' => $data]);
                    return response()->json(['error' => 'Falta slot en éxito'], 400);
                }
                
                $usuario->fingerprint_id = $slot;
                $usuario->save();

                Log::info("Huella registrada exitosamente para usuario #{$userId}, slot #{$slot}");

                return response()->json(['success' => true, 'message' => 'Huella registrada exitosamente.']);

            } elseif ($status === 'error') {
                // 2. REGISTRO FALLIDO: El Photon falló (ej. dedos diferentes)
                
                // *** ACCIÓN CRÍTICA PARA CONSISTENCIA ***
                // El registro de usuario se creó en el dashboard (inicio del proceso),
                // pero como la huella falló, debemos eliminarlo.
                $usuario->delete(); 
                
                Log::warning("Registro de huella fallido para usuario #{$userId}. Usuario eliminado (ROLLBACK).", ['payload' => $data]);
                
                return response()->json(['success' => true, 'message' => 'Registro fallido, usuario eliminado (rollback).']);
            }
            // --- FIN LÓGICA DE MANEJO DE ESTADOS ---

            return response()->json(['error' => 'Status desconocido'], 400);

        } catch (\Throwable $e) {
            Log::error('Error al registrar huella: '.$e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}