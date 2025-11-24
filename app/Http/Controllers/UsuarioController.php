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
            $usuario->update($validatedData);
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
            
            if ($usuario->fingerprint_id) {
                try {
                    // ACTUALIZADO: Se agrega timeout(3) del codigo nuevo
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

    // 👇 LÓGICA BLINDADA CONTRA DESCONEXIONES (Traída de codigo nuevo.txt) 👇
    public function resetFingerprint($id)
    {
        $usuario = Usuario::findOrFail($id);
        $deviceId = env('PARTICLE_DEVICE_ID');
        $token = env('PARTICLE_ACCESS_TOKEN');

        try {
            // PASO 1: Intentar contactar al Photon (Con Timeout de 5 segundos)
            // Si está desconectado, esto lanzará una excepción en 5s y saltará al catch.
            $responseEnroll = Http::timeout(5)->asForm()->post(
                "https://api.particle.io/v1/devices/{$deviceId}/enroll-fingerprint",
                [
                    'access_token' => $token,
                    'args' => (string) $usuario->id,
                ]
            );

            // Verificar si la API respondió con error (ej. 400 o 408)
            if ($responseEnroll->failed()) {
                throw new \Exception("El sensor no respondió a la solicitud.");
            }

            // --- SI PASAMOS AQUÍ, EL SENSOR ESTÁ VIVO ---

            // PASO 2: Borrar huella vieja (si existe)
            if ($usuario->fingerprint_id) {
                try {
                    // Se agrega timeout(3) aquí también
                    Http::timeout(3)->asForm()->post(
                        "https://api.particle.io/v1/devices/{$deviceId}/delete-fingerprint",
                        ['access_token' => $token, 'args' => (string) $usuario->fingerprint_id]
                    );
                } catch (\Throwable $e) {
                    Log::warning("No se pudo borrar la huella anterior (no crítico).");
                }
            }

            // PASO 3: Actualizar BD (Solo ahora es seguro)
            $usuario->fingerprint_id = null;
            $usuario->estatus = 0; // 0 = Esperando huella
            $usuario->save();

            // PASO 4: Job de Timeout
            CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60));

            // Éxito: Esto activará el modal de carga en el frontend
            return back()->with('success', '✅ Instrucción enviada. Siga las indicaciones en el sensor.');

        } catch (\Exception $e) {
            // 🛑 CATCH: Si falló el Paso 1 (Timeout/Error), caemos aquí.
            // La base de datos NO SE TOCÓ. El usuario sigue con su huella vieja.
            Log::error("Error al intentar actualizar huella: " . $e->getMessage());
           
            // Devolvemos 'error' para que el modal muestre la X Roja inmediatamente
            return back()->with('error', '❌ No se pudo conectar con el sensor. Verifique que esté conectado a internet.');
        }
    }
}