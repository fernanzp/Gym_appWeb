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
            ->select('u.id','u.nombre_comp', 'u.email', 'u.telefono','lm.estatus as membresia_estatus')
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
            // Usamos timeout corto (3s) para no colgar la página si está offline
            if ($usuario->fingerprint_id) {
                try {
                    Http::timeout(3)->asForm()->post(
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

    // 👇 ESTA ES LA FUNCIÓN CORREGIDA PARA EL BUG DE "FALSA DESCONEXIÓN" 👇
    public function resetFingerprint($id)
    {
        $usuario = Usuario::findOrFail($id);
        $deviceId = env('PARTICLE_DEVICE_ID');
        $token = env('PARTICLE_ACCESS_TOKEN');

        // BLOQUE DE INTENTO: Pedir perdón, no permiso.
        try {
            
            // 1. Intentar iniciar el modo "Enroll" PRIMERO.
            // 🔥 IMPORTANTE: Usamos timeout(5) para fallar rápido si está desconectado.
            $responseEnroll = Http::timeout(5)->asForm()->post(
                "https://api.particle.io/v1/devices/{$deviceId}/enroll-fingerprint",
                [
                    'access_token' => $token,
                    'args' => (string) $usuario->id,
                ]
            );

            // Verificar si la API de Particle dio error real o si el firmware no devolvió 1
            if ($responseEnroll->failed() || $responseEnroll->json('return_value') !== 1) {
                throw new \Exception("El sensor no respondió correctamente. Verifique que esté conectado.");
            }

            // --- SI LLEGAMOS AQUÍ, EL DISPOSITIVO ESTÁ VIVO Y TRABAJANDO ---

            // 2. Borrar la huella vieja (si existe)
            if ($usuario->fingerprint_id) {
                try {
                    // Timeout corto para borrar
                    Http::timeout(3)->asForm()->post(
                        "https://api.particle.io/v1/devices/{$deviceId}/delete-fingerprint",
                        ['access_token' => $token, 'args' => (string) $usuario->fingerprint_id]
                    );
                } catch (\Throwable $e) {
                    Log::warning("No se pudo borrar la huella anterior (posiblemente ya no existía).");
                }
            }

            // 3. Actualizar la Base de Datos
            // Ahora es seguro borrar el ID local porque sabemos que el proceso físico inició exitosamente.
            $usuario->fingerprint_id = null;
            $usuario->estatus = 0; // 0 = Esperando huella
            $usuario->save();

            // 4. Disparar Job de seguridad (Timeout)
            CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60));

            // Mensaje de éxito (que activa el modal de carga en el frontend)
            return back()->with('success', '✅ Instrucción enviada. Siga las indicaciones en el sensor.');

        } catch (\Exception $e) {
            // 🛑 CATCH DE SEGURIDAD
            // Si falla la conexión en el paso 1, caemos aquí.
            // La BD no se tocó, así que el usuario NO pierde su huella anterior.
            Log::error("Error al intentar actualizar huella: " . $e->getMessage());
            
            // Devolvemos error para que el JS muestre el modal de Error inmediatamente
            return back()->with('error', '❌ No se pudo conectar con el sensor. Inténtelo de nuevo en unos segundos.');
        }
    }
}