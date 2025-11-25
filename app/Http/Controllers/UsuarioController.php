<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; 
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
        // Validación básica
        $validatedData = $request->validate([
            'nombre_comp' => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:usuarios,email,' . $usuario->id,
            'telefono'    => 'required|numeric|digits_between:10,15|unique:usuarios,telefono,' . $usuario->id,
            'estatus'     => 'required|in:0,1',
        ]);

        try {
            $usuario->update($validatedData);
            return back()->with('success', 'Información actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error("Error update usuario {$usuario->id}: " . $e->getMessage());
            return back()->withErrors(['general' => 'Ocurrió un error al guardar los cambios.']);
        }
    }

    public function destroy(Usuario $usuario)
    {
        $current = auth()->user();

        // Validaciones de permisos (simplificadas para lectura)
        $tienePermiso = $current->roles()->whereIn('rol', ['admin','staff'])->exists();
        if (!$tienePermiso) return back()->withErrors(['general' => 'No tienes permisos.']);
        if ($usuario->id === $current->id) return back()->withErrors(['general' => 'No puedes eliminarte a ti mismo.']);

        $esAdmin = DB::table('roles_usuarios as ru')
            ->join('roles as r','r.id','=','ru.rol_id')
            ->where('ru.usuario_id', $usuario->id)
            ->where('r.rol','admin')
            ->exists();

        if ($esAdmin) return back()->withErrors(['general' => 'No puedes eliminar un administrador.']);

        DB::beginTransaction();
        try {
            $deviceId = env('PARTICLE_DEVICE_ID');
            $token = env('PARTICLE_ACCESS_TOKEN');
            
            if ($usuario->fingerprint_id) {
                try {
                    Http::timeout(5)->asForm()->post(
                        "https://api.particle.io/v1/devices/{$deviceId}/delete-fingerprint",
                        ['access_token' => $token, 'args' => (string) $usuario->fingerprint_id]
                    );
                } catch (\Throwable $e) {
                    Log::warning("No se pudo borrar huella del sensor (posiblemente offline).");
                }
            }

            DB::table('roles_usuarios')->where('usuario_id', $usuario->id)->delete();
            DB::table('membresias')->where('usuario_id', $usuario->id)->delete();
            $usuario->delete();

            DB::commit();
            return redirect()->route('usuarios')->with('success', 'Usuario eliminado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'No se pudo eliminar el usuario.']);
        }
    }

    // 🔥 FUNCIÓN BLINDADA CONTRA BUGS DE CONEXIÓN 🔥
    public function resetFingerprint($id)
    {
        $usuario = Usuario::findOrFail($id);
        $deviceId = env('PARTICLE_DEVICE_ID');
        $token = env('PARTICLE_ACCESS_TOKEN');

        try {
            // 1. PING INFORMATIVO (NO BLOQUEANTE)
            // Esto soluciona el bug de "Reconexión Rápida".
            try {
                $responseStatus = Http::timeout(3)->get("https://api.particle.io/v1/devices/{$deviceId}?access_token={$token}");
                $isConnected = $responseStatus->json()['connected'] ?? false;
                if (!$isConnected) {
                    Log::warning("API reporta OFFLINE, pero intentaremos forzar comando.");
                }
            } catch (\Exception $e) {
                Log::warning("Ping fallido, continuando...");
            }

            // 2. COMANDO REAL (PRUEBA DE FUEGO)
            // Timeout de 15s. Si desconectaste el cable, esto fallará aquí y saltará al catch.
            $responseEnroll = Http::timeout(15)->asForm()->post(
                "https://api.particle.io/v1/devices/{$deviceId}/enroll-fingerprint",
                [
                    'access_token' => $token,
                    'args' => (string) $usuario->id,
                ]
            );

            // 3. VALIDACIÓN ESTRICTA
            $data = $responseEnroll->json();
            
            // Si la petición HTTP falló O si no hay return_value, asumimos error de conexión.
            if ($responseEnroll->failed() || !isset($data['return_value'])) {
                throw new \Exception("El sensor no respondió al comando (Timeout o Error de Red).");
            }

            if ($data['return_value'] == -1) {
                throw new \Exception("El sensor reportó un error interno.");
            }

            // --- SI LLEGAMOS AQUÍ, TODO FUNCIONÓ ---

            // 4. Limpieza anterior
            if ($usuario->fingerprint_id) {
                try {
                    Http::timeout(5)->asForm()->post(
                        "https://api.particle.io/v1/devices/{$deviceId}/delete-fingerprint",
                        ['access_token' => $token, 'args' => (string) $usuario->fingerprint_id]
                    );
                } catch (\Throwable $e) {}
            }

            // 5. Update DB
            $usuario->fingerprint_id = null;
            $usuario->estatus = 0; 
            $usuario->save();

            CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60));

            // Retornamos éxito (activará el loader en JS)
            return back()->with('success', '✅ Instrucción enviada. Siga las indicaciones en el sensor.');

        } catch (\Exception $e) {
            // 🛑 SI FALLÓ EL PASO 2, CAEMOS AQUÍ
            Log::error("Fallo conexión Particle: " . $e->getMessage());
            
            // Retornamos error (activará el modal ROJO en JS)
            return back()->with('error', 'No se pudo conectar con el sensor. Verifique la conexión a internet del dispositivo.');
        }
    }
}