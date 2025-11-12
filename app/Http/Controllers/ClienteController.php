<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Plan;
use App\Models\Membresia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail; // Importar Mail
use Illuminate\Support\Str;           // Importar Str para el token
use App\Mail\ActivarCuentaMail;     // Importar el Mailable que crearemos
use Carbon\Carbon;

class ClienteController extends Controller
{
    public function create()
    {
        // Pobla el select de planes
        $planes = Plan::orderBy('precio')->get(['id','nombre','duracion_dias','precio']);
        return view('clientRegister', compact('planes'));
    }

    public function store(Request $request)
    {
        // 1. VALIDACIÓN (sin contraseña)
        $request->validate([
            'nombre_comp'   => ['required','string','max:160'],
            // Email ahora es OBLIGATORIO para enviar el enlace
            'email'         => ['required','email','max:160','unique:usuarios,email'],
            'telefono'      => ['nullable','regex:/^\d{10}$/'],
            'fecha_nac'     => ['nullable','date'],
            'plan_id'       => ['required','exists:planes,id'],
            // 'contrasena' ya no se valida aquí
        ],[
            'telefono.regex' => 'El teléfono debe tener exactamente 10 dígitos.',
            'email.required' => 'El email es obligatorio para enviar la activación.',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        DB::beginTransaction();
        try {
            // 2. CREAR USUARIO (inactivo y sin contraseña)
            $usuario = Usuario::create([
                'nombre_comp' => $request->nombre_comp,
                'email'       => $request->email,
                'telefono'    => $request->telefono,
                'fecha_nac'   => $request->fecha_nac,
                'contrasena'  => null, // Contraseña nula
                'estatus'     => 0, // 0 = inactivo
            ]);

            // 3. CREAR MEMBRESÍA
            $inicio = Carbon::today();
            $fin    = (clone $inicio)->addDays($plan->duracion_dias);

            Membresia::create([
                'usuario_id' => $usuario->id,
                'plan_id'    => $plan->id,
                'fecha_ini'  => $inicio->toDateString(),
                'fecha_fin'  => $fin->toDateString(),
                'estatus'    => 1, // 1 = vigente (según tu db.sql)
            ]);

            // 4. ASIGNAR ROL
            $rolMember = Rol::firstOrCreate(['rol' => 'member']);
            $usuario->roles()->syncWithoutDetaching([$rolMember->id]);

            // 5. GENERAR TOKEN Y ENVIAR EMAIL
            $token = Str::random(64);

            DB::table('password_resets')->updateOrInsert(
                ['email' => $usuario->email],
                ['token' => $token, 'created_at' => Carbon::now()]
            );

            // Generamos la URL de activación
            $urlActivacion = route('activacion.show', ['token' => $token, 'email' => $usuario->email]);
            
            // Enviamos el correo
            Mail::to($usuario->email)->send(new ActivarCuentaMail($usuario, $urlActivacion));

            DB::commit();

            return redirect()
                ->route('dashboard') // O a donde quieras
                ->with('success', 'Cliente registrado. Se ha enviado un correo de activación a ' . $usuario->email);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            if (app()->environment('local')) {
                throw $e;
            }

            return back()
                ->withInput()
                ->withErrors(['general' => 'Ocurrió un error al registrar al cliente. Intenta nuevamente.']);
        }
    }
}