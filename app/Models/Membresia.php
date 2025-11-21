<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Importante para manejar fechas

class Membresia extends Model
{
    protected $table = 'membresias';
    public $timestamps = false;
    
    protected $fillable = [
        'usuario_id','plan_id','fecha_ini','fecha_fin','dias_congelados','estatus','creada','actualizada'
    ];

    protected $casts = [
        'fecha_ini' => 'datetime',
        'fecha_fin' => 'datetime',
        'creada' => 'datetime',
        'actualizada' => 'datetime',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    // Un "scope" para filtrar fácilmente por estatus si lo necesitas
    // 1=vigente, 0=vencida, 2=congelada
    public function scopeVigentes($query) {
        return $query->where('estatus', 1);
    }
}