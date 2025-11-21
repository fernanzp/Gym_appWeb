<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Membresia;
use App\Models\RegistroAcceso;          
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log; // 🔥 Agregado para logs
use Carbon\Carbon; // 🔥 Agregado para fechas

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
        $direccion = null;

        if (!$usuario) {
            $responseReason = 'Fingerprint not registered';
        } else {
            $membresiaActiva = Membresia::where('usuario_id', $usuario->id)
                                        ->where('estatus', 'vigente')
                                        ->where('fecha_fin', '>=', now()->toDateString())
                                        ->exists();

            if ($membresiaActiva) {
                $isAllowed = true;
                $responseStatus = 'allowed';

                if ($usuario->is_inside) {
                    // Si ya estaba adentro → salida
                    $responseReason = 'Access granted (exit)';
                    $direccion = 0;
                    $usuario->is_inside = false;
                } else {
                    // Si estaba fuera → entrada
                    $responseReason = 'Access granted (entry)';
                    $direccion = 1;
                    $usuario->is_inside = true;
                }

                // Guardamos el nuevo estado
                $usuario->save();
            } else {
                $responseReason = 'Membership expired or inactive';
            }
        }

        RegistroAcceso::create([
            'fecha' => now(),
            'usuario_id' => $usuario->id ?? null,
            'acceso' => $isAllowed,
            'observaciones' => $responseReason,
            'direccion' => $direccion ?? 1,
        ]);

        $dataParaPhoton = [
            'status' => $responseStatus,
            'direction' => $direccion,
        ];

        // Enviar respuesta al Photon (Webhook response o evento)
        // Nota: Si estás usando Webhooks de Particle, la respuesta JSON al final es lo que importa.
        // Si necesitas publicar un evento de vuelta, descomenta lo siguiente:
        
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

        // 2. ID del Usuario "Visita" (El que creaste con el Seeder: 99999)
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

            // 4. Ordenar al Photon que abra el torniquete (Sin validar huella)
            try {
                 // Usamos timeout(5) para no colgar la página si el Photon está desconectado
                 Http::timeout(5)->asForm()->post(
                    'https://api.particle.io/v1/devices/' . env('PARTICLE_DEVICE_ID') . '/open-door',
                    [
                        'access_token' => env('PARTICLE_ACCESS_TOKEN'),
                        'args' => $tipo, // 'entry' o 'exit'
                    ]
                );
            } catch (\Exception $e) {
                Log::error("Error abriendo torniquete remoto para visita: " . $e->getMessage());
                // No retornamos error al usuario porque el registro en BD fue exitoso, 
                // solo logueamos que el torniquete no abrió automáticamente.
            }

            return back()->with('success', ($tipo == 'entry' ? 'Entrada' : 'Salida') . ' de visita registrada correctamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al registrar la visita: ' . $e->getMessage());
        }
    }
}