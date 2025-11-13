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
use Illuminate\Support\Facades\Http; // <-- Agregar esto arriba
use Illuminate\Support\Facades\Log;  // Para depurar si quieres

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
        $usuario = Usuario::create([
            'nombre_comp' => $request->nombre_comp,
            'email'       => $request->email,
            'telefono'    => $request->telefono,
            'fecha_nac'   => $request->fecha_nac,
            'contrasena'  => null,
            'estatus'     => 0,
        ]);

        $inicio = Carbon::today();
        $fin    = (clone $inicio)->addDays($plan->duracion_dias);

        Membresia::create([
            'usuario_id' => $usuario->id,
            'plan_id'    => $plan->id,
            'fecha_ini'  => $inicio->toDateString(),
            'fecha_fin'  => $fin->toDateString(),
            'estatus'    => 1,
        ]);

        $rolMember = Rol::firstOrCreate(['rol' => 'member']);
        $usuario->roles()->syncWithoutDetaching([$rolMember->id]);

        // Token para activar cuenta
        $token = Str::random(64);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $usuario->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        $urlActivacion = route('activacion.show', ['token' => $token, 'email' => $usuario->email]);
        Mail::to($usuario->email)->send(new ActivarCuentaMail($usuario, $urlActivacion));

        // 🔹 Aquí viene la nueva parte: enviar evento al Photon para registrar huella
        $event = 'enroll-fingerprint'; // Este nombre lo usarás en tu firmware
        $data = json_encode(['user_id' => $usuario->id]);

        Http::withHeaders([
            'Authorization' => 'Bearer ' . env('PARTICLE_ACCESS_TOKEN'),
        ])->post('https://api.particle.io/v1/devices/' . env('PARTICLE_DEVICE_ID') . '/' . $event, [
            'arg' => $data,
        ]);

        DB::commit();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Cliente registrado. Se ha enviado correo de activación y el sensor está listo para registrar su huella.');
    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);
        return back()->withInput()->withErrors(['general' => 'Ocurrió un error al registrar al cliente.']);
    }
}
}