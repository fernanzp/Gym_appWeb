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
            $status = $data['status'] ?? 'unknown'; 

            if (!$userId || $status === 'unknown') {
                Log::warning('Datos inválidos recibidos del Photon', ['payload' => $data]);
                return response()->json(['error' => 'Datos inválidos'], 400);
            }

            $usuario = Usuario::find($userId);
            if (!$usuario) {
                Log::warning('Usuario no encontrado para huella', ['user_id' => $userId]);
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            if ($status === 'success') {
                if (!$slot) {
                    Log::error('Status es Success pero falta Slot.', ['payload' => $data]);
                    return response()->json(['error' => 'Falta slot en éxito'], 400);
                }

                // 🔥 CORRECCIÓN CRÍTICA: Manejo de Duplicados
                // Verificar si este slot (ID de huella) ya pertenece a alguien más
                $conflicto = Usuario::where('fingerprint_id', $slot)
                                    ->where('id', '!=', $userId)
                                    ->first();

                if ($conflicto) {
                    Log::warning("Conflicto de ID #{$slot}. Se elimina del usuario antiguo #{$conflicto->id} para asignarlo al #{$userId}.");
                    // Quitamos la huella al usuario anterior para evitar error SQL 1062
                    $conflicto->fingerprint_id = null;
                    $conflicto->save();
                }

                // Ahora es seguro guardar
                $usuario->fingerprint_id = $slot;
                $usuario->estatus = 1; // Activo (éxito)
                $usuario->save();

                Log::info("Huella registrada exitosamente para usuario #{$userId}, slot #{$slot}");
                return response()->json(['success' => true, 'message' => 'Huella registrada.']);

            } elseif ($status === 'error') {
                $usuario->estatus = 8; // Error
                $usuario->save(); 
                
                // Limpiamos slot si acaso se envió algo basura
                $usuario->fingerprint_id = null;
                $usuario->save();
                
                Log::warning("Registro fallido para usuario #{$userId}. Estatus 8.");
                return response()->json(['success' => true, 'message' => 'Error registrado.']);
            }

            return response()->json(['error' => 'Status desconocido'], 400);

        } catch (\Throwable $e) {
            Log::error('Error al registrar huella: '.$e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}