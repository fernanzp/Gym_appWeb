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
        // Aquí iría tu lógica de actualización de datos personales...
        return back()->withErrors(['general' => 'La funcionalidad de guardar cambios está pendiente de implementación.']);
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
            // 1. Validar conexión antes de intentar borrar
            // Si no hay conexión, advertimos pero permitimos borrar de BD para no bloquear al admin
            $deviceId = env('PARTICLE_DEVICE_ID');
            $token = env('PARTICLE_ACCESS_TOKEN');
            $deviceConnected = false;

            try {
                $response = Http::get("https://api.particle.io/v1/devices/{$deviceId}?access_token={$token}");
                $deviceConnected = $response->json('connected');
            } catch (\Exception $e) {
                Log::warning("No se pudo verificar estado del Photon al eliminar usuario.");
            }

            if ($usuario->fingerprint_id && $deviceConnected) {
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
                    Log::error("Error al borrar huella física: " . $e->getMessage());
                }
            } elseif ($usuario->fingerprint_id) {
                 Log::warning("Usuario eliminado de BD pero Photon desconectado. Huella fantasma ID {$usuario->fingerprint_id} permanece en sensor.");
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

    // 👇 ESTA ES LA FUNCIÓN CORREGIDA PARA EVITAR EL BUG DE "DESCONECTADO" 👇
    public function resetFingerprint($id)
    {
        $usuario = Usuario::findOrFail($id);
        $deviceId = env('PARTICLE_DEVICE_ID');
        $token = env('PARTICLE_ACCESS_TOKEN');

        // 1. 🛡️ VALIDACIÓN CRÍTICA: ¿El dispositivo está conectado?
        // Si no está conectado, NO HACEMOS NADA. Así evitas borrar el ID de la BD.
        try {
            $response = Http::get("https://api.particle.io/v1/devices/{$deviceId}?access_token={$token}");
            
            if ($response->failed() || !$response->json('connected')) {
                // 🛑 ABORTAR MISIÓN
                return back()->with('error', '❌ Error: El sensor está DESCONECTADO o sin internet. No se puede actualizar la huella.');
            }
        } catch (\Exception $e) {
            return back()->with('error', '❌ Error de conexión con la nube Particle. Verifique su internet.');
        }

        // --- SI LLEGAMOS AQUÍ, EL DISPOSITIVO ESTÁ LISTO ---

        // 2. Intentar borrar la huella vieja del sensor físico
        if ($usuario->fingerprint_id) {
            try {
                Http::asForm()->post(
                    "https://api.particle.io/v1/devices/{$deviceId}/delete-fingerprint",
                    [
                        'access_token' => $token,
                        'args' => (string) $usuario->fingerprint_id,
                    ]
                );
            } catch (\Throwable $e) {
                Log::error("Fallo al borrar huella antigua (no crítico): " . $e->getMessage());
            }
        }

        // 3. Limpiar la BD (Ahora es seguro hacerlo)
        $usuario->fingerprint_id = null;
        $usuario->estatus = 0; // Reiniciar estatus
        $usuario->save();

        // 4. Iniciar Modo Registro
        try {
            Http::asForm()->post(
                "https://api.particle.io/v1/devices/{$deviceId}/enroll-fingerprint",
                [
                    'access_token' => $token,
                    'args' => (string) $usuario->id,
                ]
            );

            // Disparar Job de Timeout
            CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60));

            // Mensaje de éxito (que activa el modal de carga en el frontend)
            return back()->with('success', '✅ Instrucción enviada. Siga las indicaciones en el sensor.');

        } catch (\Throwable $e) {
            // Si falla el inicio de registro, revertimos un poco el daño (opcional)
            return back()->with('error', 'Error al iniciar el modo registro en el dispositivo.');
        }
    }
}