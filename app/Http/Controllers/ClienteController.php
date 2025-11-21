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
                'contrasena'  => null, 
                'estatus'     => 0,    
            ]);

            // 3️⃣ Crear membresía
            $inicio = \Carbon\Carbon::today();
            $fin    = (clone $inicio)->addDays($plan->duracion_dias);

            \App\Models\Membresia::create([
                'usuario_id' => $usuario->id,
                'plan_id'    => $plan->id,
                'fecha_ini'  => $inicio->toDateString(),
                'fecha_fin'  => $fin->toDateString(),
                'estatus'    => 1,
            ]);

            // 4️⃣ Asignar rol
            $rolMember = \App\Models\Rol::firstOrCreate(['rol' => 'member']);
            $usuario->roles()->syncWithoutDetaching([$rolMember->id]);

            DB::commit();

            // 5️⃣ Token y Correo (Omitido por brevedad, asumo que sigue igual)
            // ... tu código de mail ...

            // 6️⃣ Enviar evento a Particle (Timeout de 5s para no bloquear)
            try {
                Http::timeout(5)->asForm()->post(
                    'https://api.particle.io/v1/devices/' . env('PARTICLE_DEVICE_ID') . '/enroll-fingerprint',
                    [
                        'access_token' => env('PARTICLE_ACCESS_TOKEN'),
                        'args' => (string) $usuario->id,
                    ]
                );
            } catch (\Throwable $e) {
                Log::error("No se pudo iniciar sensor: " . $e->getMessage());
            }
            
            Log::info('Cliente creado via AJAX', ['user_id' => $usuario->id]);

            // 7️⃣ RESPUESTA JSON (Para que el JS sepa qué usuario monitorear)
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'user_id' => $usuario->id,
                    'message' => 'Usuario creado. Esperando huella.'
                ]);
            }

            // Fallback normal (si js falla)
            return redirect()->route('usuarios.edit', $usuario->id)->with('success', 'Registrado.');

        } catch (\Throwable $e) {
            DB::rollBack();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()], 500);
            }

            return back()->withInput()->withErrors(['general' => 'Error al registrar.']);
        }
    }
    public function retryEnroll(int $userId)
    {
        $usuario = Usuario::findOrFail($userId);

        try {
            // Opcional: Volver a poner el estatus en 0 (Inicial)
            $usuario->estatus = 0; 
            $usuario->save();
            
            // 1. Volver a llamar a la función de Particle para iniciar el sensor
            $response = Http::asForm()->post(
                'https://api.particle.io/v1/devices/' . env('PARTICLE_DEVICE_ID') . '/enroll-fingerprint',
                [
                    'access_token' => env('PARTICLE_ACCESS_TOKEN'),
                    'args' => (string) $usuario->id,
                ]
            );

            // 2. Volver a despachar el Job de limpieza (por si hay un nuevo timeout)
            CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60)); 

            Log::info('Reintento de enroll exitoso.', ['user_id' => $usuario->id, 'body' => $response->body()]);

            return back()->with('success', 'El proceso de registro de huella ha sido reiniciado. Por favor, coloque el dedo en el sensor.');

        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['general' => 'Ocurrió un error al intentar reiniciar el proceso de huella.']);
        }
    }
}

