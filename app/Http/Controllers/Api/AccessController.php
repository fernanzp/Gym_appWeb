<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Membresia;
use App\Models\RegistroAcceso;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AccessController extends Controller
{
    public function validateFingerprint(Request $request)
    {
        $validatedData = $request->validate([
            'fingerprint_id' => 'required|integer',
        ]);
        $fingerprintId = $validatedData['fingerprint_id'];

        $usuario = Usuario::where('fingerprint_id', $fingerprintId)->first();

        $responseStatus = 'denied';
        $responseReason = 'Unknown';
        $isAllowed = false;
        
        // Inicializamos en null para detectar si asignamos un valor o no
        $direccion = null; 

        if (!$usuario) {
            $responseReason = 'Fingerprint not registered';
            // Opcional: Podrías poner $direccion = 3 para "Huella desconocida"
            // De momento lo dejamos null y abajo tomará el default 1 o lo que decidas.
        } else {
            $membresiaActiva = Membresia::where('usuario_id', $usuario->id)
                                    ->where('estatus', 'vigente')
                                    ->where('fecha_fin', '>=', now()->toDateString())
                                    ->exists();

            if ($membresiaActiva) {
                $isAllowed = true;
                $responseStatus = 'allowed';

                if ($usuario->is_inside) {
                    // Si ya estaba adentro → salida (0)
                    $responseReason = 'Access granted (exit)';
                    $direccion = 0;
                    $usuario->is_inside = false;
                } else {
                    // Si estaba fuera → entrada (1)
                    $responseReason = 'Access granted (entry)';
                    $direccion = 1;
                    $usuario->is_inside = true;
                }

                // Guardamos el nuevo estado (adentro/afuera)
                $usuario->save();
                
            } else {
                // 🛑 CASO MEMBRESÍA VENCIDA
                $responseReason = 'Membership expired or inactive';
                
                // 🔥 AQUÍ ESTÁ EL CAMBIO:
                // Asignamos '2' para identificar intento fallido por vencimiento.
                $direccion = 2; 
            }
        }

        RegistroAcceso::create([
            'fecha' => now(),
            'usuario_id' => $usuario->id ?? null,
            'acceso' => $isAllowed,
            'observaciones' => $responseReason,
            // Si $direccion es 2 (vencido), guarda 2.
            // Si $direccion es 0 o 1 (valido), guarda eso.
            // Si es null (huella no encontrada), guarda 1 por defecto (o cámbialo a 3 si quieres).
            'direccion' => $direccion ?? 1, 
        ]);

        $dataParaPhoton = [
            'status' => $responseStatus,
            'direction' => $direccion, // El Photon recibirá 2 si está vencida
        ];

        // Enviar respuesta al Photon (Evento)
        Http::withToken(env('PARTICLE_ACCESS_TOKEN'))->post(
            'https://api.particle.io/v1/devices/events',
            [
                'name' => 'gymflow-response',
                'data' => json_encode($dataParaPhoton),
                'private' => true,
            ]
        );

        return response()->json([
            'status' => $responseStatus,
            'reason' => $responseReason,
            'direction' => $direccion,
        ]);
    }

    // 🔥 NUEVO MÉTODO PARA VISITAS (BOTONES DASHBOARD) 🔥
    public function registrarVisitaManual(Request $request)
    {
        // 1. Definir dirección (viene del botón: 'entry' o 'exit')
        $tipo = $request->input('direction'); 
        $dirInt = ($tipo === 'entry') ? 1 : 0; // 1 = Entrada, 0 = Salida

        // 2. ID del Usuario "Visita" (99999)
        $visitaId = 99999; 

        try {
            // 3. Insertar registro en la tabla de historial
            RegistroAcceso::create([
                'usuario_id' => $visitaId,
                'fecha' => Carbon::now(),
                'acceso' => 1, // 1 = Acceso concedido
                'direccion' => $dirInt,
                'observaciones' => 'Visita Casual (Manual Dashboard)'
            ]);

            // 4. Ordenar al Photon que abra el torniquete
            try {
                 Http::timeout(5)->asForm()->post(
                    'https://api.particle.io/v1/devices/' . env('PARTICLE_DEVICE_ID') . '/open-door',
                    [
                        'access_token' => env('PARTICLE_ACCESS_TOKEN'),
                        'args' => $tipo, // 'entry' o 'exit'
                    ]
                );
            } catch (\Exception $e) {
                Log::error("Error abriendo torniquete remoto para visita: " . $e->getMessage());
            }

            return back()->with('success', ($tipo == 'entry' ? 'Entrada' : 'Salida') . ' de visita registrada correctamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al registrar la visita: ' . $e->getMessage());
        }
    }
}