<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'planes';
    public $timestamps = false; // según el .sql
    protected $fillable = ['nombre','descripcion','duracion_dias','precio'];
}
