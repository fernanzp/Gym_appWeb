<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Plan;
use App\Models\Membresia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ActivarCuentaMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Jobs\CleanupIncompleteUser;

class ClienteController extends Controller
{
    public function create()
    {
        $planes = Plan::orderBy('precio')->get(['id','nombre','duracion_dias','precio']);
        return view('clientRegister', compact('planes'));
    }

    public function store(Request $request)
    {
        // 1️⃣ Validación de datos
        $request->validate([
            'nombre_comp'   => ['required','string','max:160'],
            'email'         => ['required','email','max:160','unique:usuarios,email'],
            'telefono'      => ['nullable','regex:/^\d{10}$/'],
            'fecha_nac'     => ['nullable','date'],
            'plan_id'       => ['required','exists:planes,id'],
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        DB::beginTransaction();
        try {
            // 2️⃣ Crear usuario
            $usuario = Usuario::create([
                'nombre_comp' => $request->nombre_comp,
                'email'       => $request->email,
                'telefono'    => $request->telefono,
                'fecha_nac'   => $request->fecha_nac,
                'contrasena'  => null, // pendiente hasta activación
                'estatus'     => 0,    // inactivo
            ]);

            // 3️⃣ Crear membresía
            $inicio = Carbon::today();
            $fin    = (clone $inicio)->addDays($plan->duracion_dias);

            Membresia::create([
                'usuario_id' => $usuario->id,
                'plan_id'    => $plan->id,
                'fecha_ini'  => $inicio->toDateString(),
                'fecha_fin'  => $fin->toDateString(),
                'estatus'    => 1,
            ]);

            // 4️⃣ Asignar rol
            $rolMember = Rol::firstOrCreate(['rol' => 'member']);
            $usuario->roles()->syncWithoutDetaching([$rolMember->id]);

            // ✅ Confirmar cambios en BD antes de procesos externos
            DB::commit();

            // ⬇️ PROTECCIÓN TIMEOUT (Job de limpieza)
            CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60)); 

            // 5️⃣ Generar token y ENVIAR CORREO
            // (Lo hacemos aquí para asegurar que se envíe antes de cualquier respuesta JSON)
            $token = Str::random(64);
            DB::table('password_resets')->updateOrInsert(
                ['email' => $usuario->email],
                ['token' => $token, 'created_at' => Carbon::now()]
            );

            $urlActivacion = route('activacion.show', ['token' => $token, 'email' => $usuario->email]);
            
            try {
                Mail::to($usuario->email)->send(new ActivarCuentaMail($usuario, $urlActivacion));
            } catch (\Throwable $e) {
                Log::error("Error enviando correo de activación: " . $e->getMessage());
                // No detenemos el proceso, pero queda registrado el fallo del mail
            }

            // 6️⃣ Enviar evento a Particle (Con Timeout de 5s)
            try {
                Http::timeout(5)->asForm()->post(
                    'https://api.particle.io/v1/devices/' . env('PARTICLE_DEVICE_ID') . '/enroll-fingerprint',
                    [
                        'access_token' => env('PARTICLE_ACCESS_TOKEN'),
                        'args' => (string) $usuario->id,
                    ]
                );
            } catch (\Throwable $e) {
                Log::error("No se pudo iniciar sensor automáticamente: " . $e->getMessage());
            }
            
            Log::info('Cliente creado via AJAX', ['user_id' => $usuario->id]);

            // 7️⃣ RESPUESTA FINAL
            // Si la petición viene del formulario con JS (AJAX), devolvemos JSON para el modal.
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'user_id' => $usuario->id,
                    'message' => 'Usuario creado. Correo enviado y esperando huella.'
                ]);
            }

            // Fallback normal (si falla el JS o es petición normal)
            // Redirigimos a la edición para que pueda intentar la huella ahí
            return redirect()->route('usuarios.edit', $usuario->id)->with('success', 'Registrado correctamente. Instrucción enviada.');

        } catch (\Throwable $e) {
            DB::rollBack();
            
            // Manejo de error para AJAX
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()], 500);
            }

            // Manejo de error normal
            report($e);
            return back()
                ->withInput()
                ->withErrors(['general' => 'Ocurrió un error al registrar al cliente.']);
        }
    }
    
    // Método retryEnroll (para reintentos manuales)
    public function retryEnroll(int $userId)
    {
        $usuario = Usuario::findOrFail($userId);

        try {
            $usuario->estatus = 0; 
            $usuario->save();

            // Timeout de 5s también aquí
            Http::timeout(5)->asForm()->post(
                'https://api.particle.io/v1/devices/' . env('PARTICLE_DEVICE_ID') . '/enroll-fingerprint',
                [
                    'access_token' => env('PARTICLE_ACCESS_TOKEN'),
                    'args' => (string) $usuario->id,
                ]
            );

            CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60)); 

            return back()->with('success', 'El proceso de registro de huella ha sido reiniciado.');

        } catch (\Throwable $e) {
            return back()->withErrors(['general' => 'Ocurrió un error al conectar con el sensor.']);
        }
    }

    // 1. Muestra la vista de pago con datos del registro reciente
    public function vistaPagoRegistro($id)
    {
        $usuario = Usuario::findOrFail($id);
        
        // Buscamos la membresía que se creó en el método store
        $membresia = Membresia::where('usuario_id', $usuario->id)->latest()->firstOrFail();
        $plan = $membresia->plan; // Asumiendo relación en el modelo Membresia

        // Retornamos la misma vista 'payment', pero con una bandera 'contexto'
        return view('payment', [
            'membresia'     => $membresia,
            'plan'          => $plan,
            'fecha_inicio'  => $membresia->fecha_ini->format('Y-m-d'),
            'fecha_fin'     => $membresia->fecha_fin->format('Y-m-d'),
            'costo_base'    => $plan->precio,
            'total'         => $plan->precio,
            'contexto'      => 'registro_nuevo' // <--- Esto es clave para la vista
        ]);
    }

    // 2. El usuario pagó, simplemente redirigimos al dashboard
    public function finalizarRegistro($id)
    {
        // Opcional: Aquí podrías cambiar un estatus de "pendiente_pago" a "pagado" si tuvieras esa columna
        return redirect()->route('dashboard')->with('success', 'Cliente registrado y pago confirmado.');
    }

    // 3. El usuario canceló en la pantalla de pago: Borramos todo
    public function cancelarRegistro($id)
    {
        $usuario = Usuario::findOrFail($id);
        
        // ---------------------------------------------------------
        // 1. ELIMINAR HUELLA DEL SENSOR FÍSICO (Lógica importada)
        // ---------------------------------------------------------
        if ($usuario->fingerprint_id) {
            $deviceId = env('PARTICLE_DEVICE_ID');
            $token = env('PARTICLE_ACCESS_TOKEN');

            try {
                // Enviamos la petición a Particle para liberar el slot de memoria
                $response = Http::timeout(5)->asForm()->post(
                    "https://api.particle.io/v1/devices/{$deviceId}/delete-fingerprint",
                    [
                        'access_token' => $token,
                        'args' => (string) $usuario->fingerprint_id,
                    ]
                );

                if ($response->successful()) {
                    Log::info("Huella ID {$usuario->fingerprint_id} eliminada del sensor correctamente al cancelar registro.");
                } else {
                    Log::warning("El sensor respondió con error al intentar borrar huella: " . $response->body());
                }

            } catch (\Throwable $e) {
                // Usamos try/catch para que, si el sensor está desconectado, 
                // NO impida que se borre el usuario de la base de datos.
                Log::warning("No se pudo borrar huella del sensor (posiblemente offline): " . $e->getMessage());
            }
        }

        // ---------------------------------------------------------
        // 2. ELIMINAR USUARIO DE LA BASE DE DATOS
        // ---------------------------------------------------------
        try {
            // Al borrar el usuario, la BD borrará la membresía en cascada
            $usuario->delete();
            
            return redirect()->route('dashboard')->with('info', 'El registro y la huella han sido eliminados.');
            
        } catch (\Exception $e) {
            Log::error("Error al eliminar usuario de BD: " . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Error crítico al eliminar el usuario de la base de datos.');
        }
    }
}