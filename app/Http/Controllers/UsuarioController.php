<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // 🔥
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
                'general' => 'El usuario solicitado no fue encontrado.'
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
                } catch (\Throwable $e) {}
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

// En app/Http/Controllers/UsuarioController.php

public function resetFingerprint($id)
{
    $usuario = Usuario::findOrFail($id);
    $deviceId = env('PARTICLE_DEVICE_ID');
    $token = env('PARTICLE_ACCESS_TOKEN');
    
    // URL base para no repetirla
    $baseUrl = "https://api.particle.io/v1/devices/{$deviceId}";

    try {
        // ---------------------------------------------------------
        // PASO 1: LIMPIEZA PREVIA (Borrar huella vieja si existe)
        // ---------------------------------------------------------
        if ($usuario->fingerprint_id) {
            try {
                // Intentamos borrar. Timeout corto (4s) para no atorarnos.
                Http::timeout(4)->asForm()->post(
                    "{$baseUrl}/delete-fingerprint",
                    ['access_token' => $token, 'args' => (string) $usuario->fingerprint_id]
                );
                
                // 🔥 EL SECRETO: Una pausa de 1 segundo.
                // Esto deja que el Photon termine de borrar antes de pedirle que enrole.
                sleep(1); 
                
            } catch (\Throwable $e) {
                // Si falla el borrado (ej. sensor desconectado), seguimos.
                // No queremos detener el flujo por esto.
                Log::warning("No se pudo borrar huella previa o ya no existía.");
            }
        }

        // ---------------------------------------------------------
        // PASO 2: PREPARAR EL ESTADO EN BASE DE DATOS
        // ---------------------------------------------------------
        // Ponemos estatus 0 (Inactivo/Esperando) y borramos el ID de huella.
        // Tu Javascript (polling) verá esto y sabrá que estamos "Cargando".
        $usuario->fingerprint_id = null;
        $usuario->estatus = 0; 
        $usuario->save();

        // ---------------------------------------------------------
        // PASO 3: ENVIAR LA ORDEN DE "ENROLL"
        // ---------------------------------------------------------
        // OJO: Aquí NO esperamos a que el usuario ponga el dedo.
        // Solo esperamos a que el Photon diga "Recibido, voy a encender el LED".
        $response = Http::timeout(5)->asForm()->post(
            "{$baseUrl}/enroll-fingerprint",
            [
                'access_token' => $token,
                'args' => (string) $usuario->id, // Le pasamos el ID para que el Webhook sepa quién es
            ]
        );

        if ($response->failed()) {
            throw new \Exception("El sensor rechazó la conexión (¿Está offline?).");
        }

        // ---------------------------------------------------------
        // PASO 4: RED DE SEGURIDAD
        // ---------------------------------------------------------
        // Si el usuario se arrepiente y cierra la página, este Job limpiará 
        // el estado "zombie" en 60 segundos.
        CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60));

        // ---------------------------------------------------------
        // PASO 5: RESPUESTA INMEDIATA
        // ---------------------------------------------------------
        // Regresamos al navegador de inmediato.
        // El modal de "Cargando" se quedará ahí hasta que el Photon avise (vía Webhook) que terminó.
        return back()->with('success', 'Instrucción enviada. Coloque su dedo en el sensor.');

    } catch (\Exception $e) {
        Log::error("Fallo en resetFingerprint: " . $e->getMessage());
        
        // IMPORTANTE: Marcamos estatus 8 (Error) en la BD.
        // Tu Javascript detectará este '8' y mostrará el Modal Rojo automáticamente.
        $usuario->estatus = 8; 
        $usuario->save();

        return back()->with('error', 'No se pudo conectar con el sensor. Verifique que esté encendido.');
    }
}
}