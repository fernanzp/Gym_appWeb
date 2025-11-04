<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Membresia;
use App\Models\RegistroAcceso;          
use Illuminate\Support\Facades\Http; 

class AccessController extends Controller
{
    public function validateFingerprint(Request $request)
    {
        // Valida que el dispositivo IoT envió el 'fingerprint_id'
        $validatedData = $request->validate([
            'fingerprint_id' => 'required|integer',
        ]);
        $fingerprintId = $validatedData['fingerprint_id'];

        // --- Lógica de Validación (Tu código original) ---
        $usuario = Usuario::where('fingerprint_id', $fingerprintId)->first();

        $responseStatus = 'denied'; // Por defecto, es denegado
        $responseReason = 'Unknown';
        $isAllowed = false;
        $userIdToLog = null;

        if (!$usuario) {
            $responseReason = 'Fingerprint not registered';
        } else {
            $userIdToLog = $usuario->id; // Encontramos un usuario, guardamos su ID para el log

            // ✅ *** CORRECCIÓN DE BUG ***
            // Tu BD usa 'vigente', no 1.
            $membresiaActiva = Membresia::where('usuario_id', $usuario->id)
                                        ->where('estatus', 'vigente') // <-- Corregido
                                        ->where('fecha_fin', '>=', now()->toDateString())
                                        ->exists();

            if ($membresiaActiva) {
                $responseStatus = 'allowed';
                $responseReason = 'Access granted';
                $isAllowed = true;
            } else {
                $responseReason = 'Membership expired or inactive';
            }
        }
        
        // --- ✅ 3. Lógica Nueva: Registrar el Acceso en nuestra BD ---
        RegistroAcceso::create([
            'fecha' => now(),
            'usuario_id' => $userIdToLog, // Será null si la huella no se encontró
            'acceso' => $isAllowed,       // 1 (true) si fue permitido, 0 (false) si fue denegado
            'observaciones' => $responseReason,
            'direccion' => 1 // 1 = entrada
        ]);

        // --- ✅ 4. Lógica Nueva: Publicar la respuesta de regreso a la Photon ---
        $dataParaPhoton = ['status' => $responseStatus];

        Http::withToken(env('PARTICLE_ACCESS_TOKEN'))->post(
            'https://api.particle.io/v1/devices/events',
            [
                'name' => 'gymflow-response', // El evento que la Photon está escuchando
                'data' => json_encode($dataParaPhoton),
                'private' => true,
                // 'deviceid' => $request->input('coreid') // Opcional: para enviar a un dispositivo específico
            ]
        );

        // --- 5. Respuesta final al Webhook ---
        // Esto le dice a la Nube de Particle que "recibimos el webhook correctamente".
        return response()->json(['status' => $responseStatus, 'reason' => $responseReason]);
    }
}