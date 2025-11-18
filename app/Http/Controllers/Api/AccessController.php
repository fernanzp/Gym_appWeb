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

}