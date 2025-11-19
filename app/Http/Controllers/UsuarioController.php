<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // 🔥 IMPORTANTE: Agrega esto para conectar con Particle
use Illuminate\Support\Facades\Log;  // 🔥 Para logs de errores
use App\Models\Usuario;
use App\Jobs\CleanupIncompleteUser; // 🔥 Para el Job de timeout en el reset

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        // ... (Tu código index se queda IGUAL, sin cambios) ...
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
        // ... (Tu código edit se queda IGUAL) ...
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
        // ... (Tu código update se queda IGUAL por ahora) ...
        return back()->withErrors(['general' => 'La funcionalidad de guardar cambios está pendiente de implementación.']);
    }

    // 👇👇👇 AQUÍ ESTÁ LA MODIFICACIÓN IMPORTANTE DEL PASO 2 👇👇👇
    public function destroy(Usuario $usuario)
    {
        $current = auth()->user();

        // Permisos
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
            // 🔥 LÓGICA NUEVA: Borrar huella del sensor físico antes de borrar de BD
            if ($usuario->fingerprint_id) {
                try {
                    // Llamada a la función 'delete-fingerprint' del Photon
                    Http::asForm()->post(
                        'https://api.particle.io/v1/devices/' . env('PARTICLE_DEVICE_ID') . '/delete-fingerprint',
                        [
                            'access_token' => env('PARTICLE_ACCESS_TOKEN'),
                            'args' => (string) $usuario->fingerprint_id,
                        ]
                    );
                    Log::info("Orden de borrado de huella enviada al sensor para usuario ID {$usuario->id}");
                } catch (\Throwable $e) {
                    // Solo registramos el error, NO detenemos la eliminación del usuario
                    Log::error("Error al intentar borrar huella del sensor: " . $e->getMessage());
                }
            }
            // 🔥 FIN LÓGICA NUEVA

            // Eliminar relaciones
            DB::table('roles_usuarios')->where('usuario_id', $usuario->id)->delete();
            DB::table('membresias')->where('usuario_id', $usuario->id)->delete();

            // Finalmente el usuario
            $usuario->delete();

            DB::commit();
            return redirect()->route('usuarios')->with('success', 'Usuario y datos biométricos eliminados correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['general' => 'No se pudo eliminar el usuario. Revisa el log para más detalles.']);
        }
    }

    // 👇👇👇 ESTA ES LA NUEVA FUNCIÓN PARA EL PASO 3 (Resetear Huella) 👇👇👇
    public function resetFingerprint($id)
    {
        $usuario = Usuario::findOrFail($id);

        // 1. Borrar la huella vieja del sensor (si existe)
        if ($usuario->fingerprint_id) {
            try {
                Http::asForm()->post(
                    'https://api.particle.io/v1/devices/' . env('PARTICLE_DEVICE_ID') . '/delete-fingerprint',
                    [
                        'access_token' => env('PARTICLE_ACCESS_TOKEN'),
                        'args' => (string) $usuario->fingerprint_id,
                    ]
                );
            } catch (\Throwable $e) {
                Log::error("Fallo al borrar huella antigua: " . $e->getMessage());
            }
        }

        // 2. Limpiar la BD y preparar para nueva huella
        $usuario->fingerprint_id = null;
        $usuario->estatus = 0; // Reiniciar estatus para que salga del modo error/timeout
        $usuario->save();

        // 3. Iniciar Modo Registro en el Photon inmediatamente
        try {
            Http::asForm()->post(
                'https://api.particle.io/v1/devices/' . env('PARTICLE_DEVICE_ID') . '/enroll-fingerprint',
                [
                    'access_token' => env('PARTICLE_ACCESS_TOKEN'),
                    'args' => (string) $usuario->id,
                ]
            );

            // Disparar el Job de Timeout para seguridad
            CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60));

            return back()->with('success', 'Huella anterior eliminada. El sensor está listo: Coloca el nuevo dedo.');

        } catch (\Throwable $e) {
            return back()->with('error', 'Error de conexión con el dispositivo IoT: ' . $e->getMessage());
        }
    }
}