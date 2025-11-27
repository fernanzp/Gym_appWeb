<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;   
use App\Models\Usuario;
use App\Jobs\CleanupIncompleteUser; 
use Illuminate\Http\Client\ConnectionException; 
use Illuminate\Support\Str;

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
                'general' => 'El usuario solicitado no fue encontrado.'
            ]);
        }
        
        return view('editUser', compact('usuario'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        // 1. Validación de datos
        // Usamos $usuario->id para ignorar el email/teléfono del propio usuario al verificar "unique"
        $validatedData = $request->validate([
            'nombre_comp' => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:usuarios,email,' . $usuario->id,
            'telefono'    => 'nullable|numeric|digits_between:10,15|unique:usuarios,telefono,' . $usuario->id,
            'estatus'     => 'required|in:0,1', // Asegura que solo reciba 1 (Activo) o 0 (Inactivo)
        ], [
            // Mensajes personalizados (opcional)
            'nombre_comp.required'    => 'Ingresa un nombre de usuario para continuar.',
            'email.required'    => 'Ingresa un correo electrónico para continuar.',
            'email.email'       => 'Ingresa un correo electrónico valido.',
            'email.unique'      => 'Este correo ya pertenece a otro usuario.',
            'telefono.unique'   => 'Este teléfono ya pertenece a otro usuario.',
            'telefono.numeric'  => 'El teléfono solo debe contener números.'
        ]);

        try {
            // 2. Actualizar el usuario
            // Como definiste $fillable en el modelo Usuario, podemos usar update() directo.
            $usuario->update($validatedData);

            // 3. Retornar éxito
            return redirect()->route('usuarios')->with('success', 'Información del usuario actualizada correctamente.');
        } catch (\Exception $e) {
            // Log del error para depuración interna
            Log::error("Error al actualizar usuario ID {$usuario->id}: " . $e->getMessage());

            // Retornar error general para mostrar la alerta roja
            return back()->withErrors(['general' => 'Ocurrió un error al guardar los cambios en la base de datos.']);
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


public function resetFingerprint($id)
{
    $usuario = Usuario::findOrFail($id);
    $deviceId = env('PARTICLE_DEVICE_ID');
    $token = env('PARTICLE_ACCESS_TOKEN');
    $baseUrl = "https://api.particle.io/v1/devices/{$deviceId}";

    // Iniciamos la "Caja Fuerte" (Transacción)
    DB::beginTransaction();

    try {
        // Guardamos el estado original para poder restaurar
        $oldFingerprintId = $usuario->fingerprint_id;

        // 1. CAMBIO TEMPORAL EN BD (Solo se confirma si todo sale bien)
        $usuario->estatus = 7; // Estatus "En proceso"
        $usuario->save();

        // ---------------------------------------------------------
        // 2. VALIDACIÓN DE CONEXIÓN (PING RÁPIDO)
        // ---------------------------------------------------------
        try {
            $responseInfo = Http::withHeaders(['Authorization' => "Bearer {$token}"])
                ->timeout(4) // 4 segundos máx para checar conexión
                ->get($baseUrl);
            
            if (!$responseInfo->successful()) {
                throw new \Exception('No se pudo conectar con la nube de Particle.');
            }

            $info = $responseInfo->json();
            if (isset($info['connected']) && $info['connected'] === false) {
                throw new \Exception('El dispositivo Photon está APAGADO o sin internet.');
            }

        } catch (\Exception $e) {
            // Si falla el ping, abortamos antes de borrar nada
            throw $e; 
        }

        // ---------------------------------------------------------
        // 3. INTENTAR BORRAR HUELLA FÍSICA (Si existía)
        // ---------------------------------------------------------
        if ($oldFingerprintId) {
            try {
                $delResp = Http::withHeaders(['Authorization' => "Bearer {$token}"])
                    ->timeout(5)
                    ->asForm()->post("{$baseUrl}/delete-fingerprint", [
                        'args' => (string) $oldFingerprintId
                    ]);
                
                // Pequeña pausa para que el Photon procese el borrado
                sleep(1);

            } catch (\Exception $e) {
                // Si falla borrar, lanzamos error específico para que el usuario reintente
                throw new \Exception('Sincronización fallida: El sensor tiene datos antiguos.');
            }
        }

        // 4. RESET LOCAL (Borramos ID en la BD)
        $usuario->fingerprint_id = null;
        $usuario->save();

        // ---------------------------------------------------------
        // 5. INSTRUCCIÓN DE ENROLAMIENTO
        // ---------------------------------------------------------
        $fnResp = Http::withHeaders(['Authorization' => "Bearer {$token}"])
            ->timeout(12) // Damos tiempo suficiente al request
            ->asForm()->post("{$baseUrl}/enroll-fingerprint", [
                'args' => (string) $usuario->id
            ]);

        if (!$fnResp->successful()) {
            throw new \Exception('El dispositivo rechazó la instrucción (Error ' . $fnResp->status() . ').');
        }

        $body = $fnResp->json();

        // 6. VERIFICAR RESPUESTA DEL FIRMWARE
        if (isset($body['return_value']) && intval($body['return_value']) >= 0) {
            
            // ¡ÉXITO! Confirmamos los cambios en la BD
            DB::commit();

            // Job de seguridad por si el usuario abandona
            CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60));

            // Regresamos la señal para activar el Loader y el Polling JS
            return back()->with('trigger_enroll', true);
        }

        throw new \Exception('El sensor devolvió un error desconocido al iniciar.');

    } catch (ConnectionException $e) {
        // --- MANEJO DE TIMEOUTS (Photon desconectado violentamente) ---
        DB::rollBack();
        return back()->with('error', 'El dispositivo no responde. Verifique que esté conectado a la luz e internet.');

    } catch (\Throwable $e) {
        // --- MANEJO DE ERRORES GENERALES ---
        DB::rollBack();
        Log::error("Error en resetFingerprint: ".$e->getMessage());

        // Personalizamos el mensaje para que se vea bonito en el Modal Rojo
        $msg = $e->getMessage();

        if (Str::contains($msg, 'Sincronización fallida') || Str::contains($msg, 'datos antiguos')) {
            $msg = 'Sincronización incompleta: El sensor tiene huellas antiguas. Presione "Intentar de Nuevo" para forzar la limpieza.';
        } elseif (Str::contains($msg, 'connected') || Str::contains($msg, 'APAGADO')) {
            $msg = 'El dispositivo parece estar desconectado. Revise su conexión WiFi.';
        } elseif (Str::contains($msg, 'cURL error')) {
             $msg = 'Error de comunicación. El dispositivo tardó demasiado en responder.';
        }

        return back()->with('error', $msg);
    }
}

}