<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membresia extends Model
{
    protected $table = 'membresias';
    public $timestamps = false; // la tabla usa 'creada' y 'actualizada'
    protected $fillable = [
        'usuario_id','plan_id','fecha_ini','fecha_fin','estatus','creada','actualizada'
    ];
}
