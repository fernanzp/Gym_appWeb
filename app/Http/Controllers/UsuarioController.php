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

    try {
        // ---------------------------------------------------------
        // PASO 1: PING ESTRICTO (Detectar desconexión rápido)
        // ---------------------------------------------------------
        $responseInfo = Http::timeout(3)->get("{$baseUrl}?access_token={$token}");
        
        if ($responseInfo->failed()) {
            throw new \Exception("No hay conexión con el dispositivo (Ping fallido).");
        }

        $info = $responseInfo->json();
        if (isset($info['connected']) && $info['connected'] === false) {
            throw new \Exception("El dispositivo está DESCONECTADO.");
        }

        // ---------------------------------------------------------
        // PASO 2: BORRAR HUELLA EXISTENTE EN EL SENSOR (si aplica)
        // ---------------------------------------------------------
        if ($usuario->fingerprint_id) {
            try {
                Http::timeout(5)->asForm()->post(
                    "{$baseUrl}/delete-fingerprint",
                    [
                        'access_token' => $token,
                        'args' => (string) $usuario->fingerprint_id
                    ]
                );
                sleep(2);
            } catch (\Throwable $e) {
                // ignorar (el sensor podría no tenerla)
            }
        }

        // ---------------------------------------------------------
        // PASO 3: RESET EN BASE DE DATOS
        // ---------------------------------------------------------
        $usuario->fingerprint_id = null;
        $usuario->estatus = 0; 
        $usuario->save();

        // ---------------------------------------------------------
        // PASO 4: INICIAR ENROLAMIENTO EN EL PHOTON
        // ---------------------------------------------------------
        Http::timeout(8)
            ->asForm()
            ->post("{$baseUrl}/enroll-fingerprint", [
                'access_token' => $token, 
                'args' => (string) $usuario->id
            ])
            ->throw();

        // ---------------------------------------------------------
        // PASO 5: PROTECCIÓN POR SI SE ATORA
        // ---------------------------------------------------------
        CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60));

        // ---------------------------------------------------------
        // 🔥 REGRESO CORRECTO PARA EL FRONTEND
        // ---------------------------------------------------------
        return back()->with('trigger_enroll', true);

    } catch (\Exception $e) {

        Log::error("Fallo resetFingerprint: " . $e->getMessage());
        
        $usuario->estatus = 8;
        $usuario->save();

        return back()->with('error', 'Error de conexión: ' . $e->getMessage());
    }
}

}