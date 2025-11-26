<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;   
use App\Models\Usuario;
use App\Jobs\CleanupIncompleteUser; 
use Illuminate\Http\Client\ConnectionException; 

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
            'telefono'    => 'required|numeric|digits_between:10,15|unique:usuarios,telefono,' . $usuario->id,
            'estatus'     => 'required|in:0,1', // Asegura que solo reciba 1 (Activo) o 0 (Inactivo)
        ], [
            // Mensajes personalizados (opcional)
            'email.unique'    => 'Este correo ya está registrado por otro usuario.',
            'telefono.unique' => 'Este teléfono ya pertenece a otro usuario.',
            'telefono.numeric'=> 'El teléfono solo debe contener números.',
        ]);

        try {
            // 2. Actualizar el usuario
            // Como definiste $fillable en el modelo Usuario, podemos usar update() directo.
            $usuario->update($validatedData);

            // 3. Retornar éxito
            // Tu JS busca la sesión 'success'. Al no contener "Instrucción enviada",
            // mostrará el modal de éxito con la palomita verde.
            return back()->with('success', 'Información actualizada correctamente.');
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
        // Guardamos el estado original para poder restaurar en caso de error lógico
        $oldFingerprintId = $usuario->fingerprint_id;
        $oldEstatus = $usuario->estatus;

        // 1. CAMBIO TEMPORAL EN BD (Solo se confirma si todo sale bien)
        $usuario->estatus = 7; // En proceso
        $usuario->save();

        // ---------------------------------------------------------
        // 2. VALIDACIÓN DE CONEXIÓN (PING)
        // ---------------------------------------------------------
        try {
            $responseInfo = Http::withHeaders(['Authorization' => "Bearer {$token}"])
                ->timeout(4) // 4 segundos máximo para conectar
                ->get($baseUrl);
            
            if (!$responseInfo->successful()) {
                throw new \Exception('No se pudo conectar con la nube de Particle.');
            }

            $info = $responseInfo->json();
            if (isset($info['connected']) && $info['connected'] === false) {
                throw new \Exception('El dispositivo Photon está APAGADO o sin internet.');
            }

        } catch (\Exception $e) {
            // Si falla la conexión, deshacemos cambios en BD y mostramos error
            DB::rollBack(); 
            Log::warning("Ping fallido: " . $e->getMessage());
            return back()->with('error', $e->getMessage());
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
                
                // Opcional: Verificar si el sensor confirmó el borrado
                // Si falla el borrado físico, ¿queremos detener todo? 
                // Generalmente sí, para evitar "huellas fantasma".
                if ($delResp->failed()) {
                     Log::warning("El sensor no confirmó el borrado de la huella ID: $oldFingerprintId");
                }
                
                sleep(1); // Dar respiro al Photon

            } catch (\Exception $e) {
                // Si falla borrar, es riesgoso continuar. Mejor abortamos.
                DB::rollBack();
                return back()->with('error', 'No se pudo limpiar la huella anterior del sensor. Intente de nuevo.');
            }
        }

        // 4. PREPARAR DB PARA NUEVA HUELLA
        $usuario->fingerprint_id = null;
        $usuario->save();

        // ---------------------------------------------------------
        // 5. INSTRUCCIÓN DE ENROLAMIENTO (El paso crítico)
        // ---------------------------------------------------------
        try {
            $fnResp = Http::withHeaders(['Authorization' => "Bearer {$token}"])
                ->timeout(12) // Tiempo suficiente para que responda el request
                ->asForm()->post("{$baseUrl}/enroll-fingerprint", [
                    'args' => (string) $usuario->id
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // AQUÍ CACHAMOS EL TIMEOUT (cURL error 28)
            throw new \Exception('El dispositivo no respondió a tiempo (Timeout). Verifique su conexión.');
        }

        if (!$fnResp->successful()) {
            throw new \Exception('El dispositivo rechazó la instrucción (Error ' . $fnResp->status() . ').');
        }

        $body = $fnResp->json();

        // 6. VERIFICAR RESPUESTA DEL FIRMWARE
        if (isset($body['return_value']) && intval($body['return_value']) >= 0) {
            
            // ¡TODO SALIÓ BIEN! CONFIRMAMOS LOS CAMBIOS EN LA BD
            DB::commit();

            CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60));

            return back()->with('trigger_enroll', true);
        }

        // Si llegamos aquí, el firmware devolvió -1 o algo raro
        throw new \Exception('El sensor devolvió un error desconocido al iniciar.');

    } catch (\Throwable $e) {
        // "Ctrl + Z" a la base de datos
        DB::rollBack();
        
        Log::error("Error en resetFingerprint: ".$e->getMessage());

        // Mensaje amigable para el usuario en lugar del error cURL feo
        $msg =Str::contains($e->getMessage(), 'cURL error 28') 
            ? 'El dispositivo tardó demasiado en responder. Asegúrese de que esté conectado.' 
            : $e->getMessage();

        return back()->with('error', $msg);
    }
}

}