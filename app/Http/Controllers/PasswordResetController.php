<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Usuario;
use App\Mail\ResetPasswordMail; // Lo crearemos en el paso 3
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    // Muestra la vista para ingresar el correo
    public function showLinkRequestForm()
    {
        return view('auth.forgotPassword');
    }

    // Envía el correo con el enlace
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:usuarios,email']);

        $token = Str::random(64);

        // Guardamos el token en la tabla password_resets
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        $usuario = Usuario::where('email', $request->email)->first();
        
        // Generamos el link
        $link = route('password.reset', ['token' => $token, 'email' => $request->email]);

        // Enviamos el mail
        Mail::to($request->email)->send(new ResetPasswordMail($usuario, $link));

        return back()->with('status', '¡Te hemos enviado un enlace para restablecer tu contraseña!');
    }

    // Muestra el formulario para poner la nueva contraseña
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.resetPassword', ['token' => $token, 'email' => $request->email]);
    }

    // Guarda la nueva contraseña
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
            'contrasena' => 'required|min:8|confirmed',
            'token' => 'required'
        ]);

        // Validar token
        $resetRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord || Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            return back()->withErrors(['email' => 'El enlace no es válido o ha expirado.']);
        }

        // Actualizar usuario
        $usuario = Usuario::where('email', $request->email)->first();
        $usuario->contrasena = $request->contrasena; // Tu mutator en el modelo se encarga del Hash
        $usuario->save();

        // Borrar token usado
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('password.success');
    }
}