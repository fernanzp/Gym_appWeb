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

            // ✅ Confirmar cambios antes de tareas lentas
            DB::commit();
            // ⬇️ ⭐️ IMPLEMENTACIÓN DEL TIMEOUT (Bug 2 - Parte 2) ⭐️ ⬇️

            // Programar la limpieza de la DB si el registro de huella falla por timeout
            // El Job se ejecutará en 60 segundos. Si en ese tiempo no llega el evento de éxito,
            // y el campo fingerprint_id sigue null, el registro se eliminará.
            CleanupIncompleteUser::dispatch($usuario->id)->delay(now()->addSeconds(60)); 
            
            // ⬆️ ⭐️ FIN IMPLEMENTACIÓN DEL TIMEOUT ⭐️ ⬆️

            // 5️⃣ Generar token de activación
            $token = Str::random(64);
            DB::table('password_resets')->updateOrInsert(
                ['email' => $usuario->email],
                ['token' => $token, 'created_at' => Carbon::now()]
            );

            // 6️⃣ Enviar correo
            $urlActivacion = route('activacion.show', ['token' => $token, 'email' => $usuario->email]);
            Mail::to($usuario->email)->send(new ActivarCuentaMail($usuario, $urlActivacion));

          // 7️⃣ Enviar evento a Particle (modo registro de huella)
            $event = 'enroll-fingerprint'; // nombre del evento publicado en el firmware del Photon
            
            // ✅ Corrección: enviar el ID como texto plano, no JSON
            $response = Http::asForm()->post(
                'https://api.particle.io/v1/devices/' . env('PARTICLE_DEVICE_ID') . '/' . $event,
                [
                    'access_token' => env('PARTICLE_ACCESS_TOKEN'),
                    'args' => (string) $usuario->id, // 👈 debe ser string simple, no JSON
                ]
            );
            
            // 8️⃣ Log para depuración
            Log::info('Particle enroll response', [
                'user_id' => $usuario->id,
                'body' => $response->body(),
            ]);


            // 9️⃣ Redirigir con éxito
            return redirect()
                ->route('dashboard')
                ->with('success', 'Cliente registrado. Se ha enviado correo de activación y el sensor está listo para registrar su huella.');

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Ocurrió un error al registrar al cliente.']);
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

