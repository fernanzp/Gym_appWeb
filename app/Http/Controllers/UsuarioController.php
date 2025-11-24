<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // 🔥 IMPORTANTE
use Illuminate\Support\Facades\Log;   
use App\Models\Usuario;
use App\Jobs\CleanupIncompleteUser; 

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));

        $latest = DB::table('membresias as m1')
            ->select('m1.usuario_id', 'm1.estatus')
            ->join(DB::raw('(SELECT usuario_id, MAX(fecha_ini) AS max_ini FROM membresias GROUP BY usuario_id) as mm'),
                function ($join) {
                    $join->on('m1.usuario_id', '=', 'mm.usuario_id')
                             ->on('m1.fecha_ini', '=', 'mm.max_ini');
                });

        $usuarios = DB::table('usuarios as u')
            ->leftJoinSub($latest, 'lm', fn($j) => $j->on('u.id','=','lm.usuario_id'))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('u.nombre_comp','like',"%{$q}%")
                      ->orWhere('u.telefono','like',"%{$q}%")
                      ->orWhere('u.email','like',"%{$q}%");
                });
            })
            ->orderByDesc('u.created_at')
            ->select('u.id','u.nombre_comp','u.telefono','lm.estatus as membresia_estatus')
            ->paginate(10)
            ->withQueryString();

        return view('usuarios', compact('usuarios', 'q'));
    }

    public function edit($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return redirect()->route('usuarios')->withErrors([
                'general' => 'El usuario solicitado (ID: ' . $id . ') no fue encontrado.'
            ]);
        }
        
        return view('editUser', compact('usuario'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        // 1. Validación de datos
        $validatedData = $request->validate([
            'nombre_comp' => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:usuarios,email,' . $usuario->id,
            'telefono'    => 'required|numeric|digits_between:10,15|unique:usuarios,telefono,' . $usuario->id,
            'estatus'     => 'required|in:0,1',
        ], [
            'email.unique'    => 'Este correo ya está registrado por otro usuario.',
            'telefono.unique' => 'Este teléfono ya pertenece a otro usuario.',
            'telefono.numeric'=> 'El teléfono solo debe contener números.',
        ]);

        try {
            // 2. Actualizar el usuario
            $usuario->update($validatedData);

            // 3. Retornar éxito
            return back()->with('success', 'Información actualizada correctamente.');

        } catch (\Exception $e) {
            Log::error("Error al actualizar usuario ID {$usuario->id}: " . $e->getMessage());
            return back()->withErrors(['general' => 'Ocurrió un error al guardar los cambios en la base de datos.']);
        }
    }

    public function destroy(Usuario $usuario)
    {
        $current = auth()->user();

        $tienePermiso = $current->roles()
            ->whereIn('rol', ['admin','staff'])
            ->exists();

        if (!$tienePermiso) {
            return back()->withErrors(['general' => 'No tienes permisos para eliminar usuarios.']);
        }

        if ($usuario->id === $current->id) {
            return back()->withErrors(['general' => 'No puedes eliminar tu propia cuenta.']);
        }

        $esAdmin = DB::table('roles_usuarios as ru')
            ->join('roles as r','r.id','=','ru.rol_id')
            ->where('ru.usuario_id', $usuario->id)
            ->where('r.rol','admin')
            ->exists();

        if ($esAdmin) {
            return back()->withErrors(['general' => 'No se permite eliminar cuentas con rol administrador.']);
        }

        DB::beginTransaction();
        try {
            $deviceId = env('PARTICLE_DEVICE_ID');
            $token = env('PARTICLE_ACCESS_TOKEN');
            
            // Intentamos borrar del sensor sin bloquearnos por el estado "connected"
            if ($usuario->fingerprint_id) {
                try {
                    Http::asForm()->post(
                        "https://api.particle.io/v1/devices/{$deviceId}/delete-fingerprint",
                        [
                            'access_token' => $token,
                            'args' => (string) $usuario->fingerprint_id,
                        ]
                    );
                    Log::info("Orden de borrado enviada al sensor.");
                } catch (\Throwable $e) {
                    Log::warning("No se pudo borrar huella del sensor (posiblemente offline): " . $e->getMessage());
                }
            }

            DB::table('roles_usuarios')->where('usuario_id', $usuario->id)->delete();
            DB::table('membresias')->where('usuario_id', $usuario->id)->delete();
            $usuario->delete();

            DB::commit();
            return redirect()->route('usuarios')->with('success', 'Usuario eliminado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['general' => 'No se pudo eliminar el usuario.']);
        }
    }

    // 👇 ESTA ES LA VERSIÓN DEFINITIVA (CORREGIDA PARA RECONEXIÓN RÁPIDA) 👇
    public function resetFingerprint($id)
    {
        $usuario = Usuario::findOrFail($id);
        $deviceId = env('PARTICLE_DEVICE_ID');
        $token = env('PARTICLE_ACCESS_TOKEN');

        try {
            // 1. PING DE ESTADO (NO BLOQUEANTE)
            // Intentamos ver si está conectado, pero si la API dice "offline" (falso positivo),
            // solo registramos la advertencia y seguimos adelante.
            try {
                $responseStatus = Http::timeout(3)->get(
                    "https://api.particle.io/v1/devices/{$deviceId}?access_token={$token}"
                );
                
                if ($responseStatus->successful()) {
                    $isConnected = $responseStatus->json()['connected'] ?? false;
                    if (!$isConnected) {
                        Log::warning("UsuarioController: La API de Particle dice que el dispositivo está OFFLINE, pero intentaremos enviar el comando de todos modos.");
                    }
                }
            } catch (\Exception $e) {
                // Si falla la conexión al consultar estado, solo logueamos y seguimos.
                Log::warning("UsuarioController: No se pudo verificar estado previo (Ping): " . $e->getMessage());
            }

            // 2. INTENTO DE COMANDO "ENROLL" (PRUEBA DE FUEGO)
            // Aquí es donde realmente validamos.
            // Timeout de 15s: Si el cable está desconectado, esto fallará aquí.
            $responseEnroll = Http::timeout(15)->asForm()->post(
                "https://api.particle.io/v1/devices/{$deviceId}/enroll-fingerprint",
                [
                    'access_token' => $token,
                    'args' => (string) $usuario->id,
                ]
            );

            $data = $responseEnroll->json();
            
            // 3. VALIDACIÓN ESTRICTA DE LA RESPUESTA
            // Si la petición falló (timeout o red) O si el JSON no trae 'return_value'...
            if ($responseEnroll->failed() || !isset($data['return_value'])) {
                throw new \Exception("El sensor no confirmó la recepción del comando (Posiblemente desconectado).");
            }

            // Si el firmware del Photon devolvió error explícito (ej. -1)
            if ($data['return_value'] == -1) {
                throw new \Exception("El sensor reportó un error interno al iniciar el registro.");
            }

            // --- SI LLEGAMOS AQUÍ, EL DISPOSITIVO ESTÁ VIVO Y CONECTADO ---

            // 4. Borrar la huella vieja (si existe)
            if ($usuario->fingerprint_id) {
                try {
                    Http::timeout(5)->asForm()->post(
                        "https://api.particle.io/v1/devices/{$deviceId}/delete-fingerprint",
                        ['access_token' => $token, 'args' => (string) $usuario->fingerprint_id]
                    );
                } catch (\Throwable $e) {
                    Log::warning("No se pudo borrar la huella anterior (no crítico).");
                }
            }

            // 5. Actualizar la Base de Datos
            $usuario->fingerprint_id = null;
            $usuario->estatus = 0; // 0 = Esperando huella
            $usuario->save();

            // 6. Job de seguridad (Timeout)
            CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60));

            return back()->with('success', '✅ Instrucción enviada. Siga las indicaciones en el sensor.');

        } catch (\Exception $e) {
            // 🛑 CATCH FINAL
            // Si falló el paso 2 (timeout de 15s), caemos aquí.
            Log::error("Error crítico al actualizar huella: " . $e->getMessage());
            
            return back()->with('error', '❌ Error de conexión: El dispositivo no responde.');
        }
    }
}