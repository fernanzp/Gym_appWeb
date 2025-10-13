<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';
    // public $timestamps = false; // descomenta si tu tabla no tiene created_at/updated_at

    protected $fillable = ['nombre_comp','email','telefono','contrasena','fecha_nac','estatus'];
    protected $hidden  = ['contrasena','remember_token'];

    // Indica a Laravel dónde está el password
    public function getAuthPassword(){ return $this->contrasena; }

    // Hashea al asignar
    public function setContrasenaAttribute($value)
    {
        if (!$value) return;
        if (is_string($value) && strlen($value) === 60 && str_starts_with($value, '$2y$')) {
            $this->attributes['contrasena'] = $value; // ya viene hasheado
        } else {
            $this->attributes['contrasena'] = Hash::make($value);
        }
    }
}
