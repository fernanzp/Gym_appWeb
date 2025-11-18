<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    DB::table('planes')->insert([
        ['id' => 1, 'nombre' => 'Día', 'descripcion' => 'Acceso por 1 día', 'duracion_dias' => 1, 'precio' => 60],
        ['id' => 2, 'nombre' => 'Semana', 'descripcion' => 'Acceso por 7 días', 'duracion_dias' => 7, 'precio' => 180],
        ['id' => 3, 'nombre' => 'Mensual', 'descripcion' => 'Acceso por 30 días', 'duracion_dias' => 30, 'precio' => 450],
        ['id' => 4, 'nombre' => 'Trimestral', 'descripcion' => 'Acceso por 90 días (ahorro vs mensual)', 'duracion_dias' => 90, 'precio' => 1200],
        ['id' => 5, 'nombre' => 'Semestral', 'descripcion' => 'Acceso por 180 días (ahorro extra)', 'duracion_dias' => 180, 'precio' => 2200],
        ['id' => 6, 'nombre' => 'Anual', 'descripcion' => 'Acceso por 365 días (mejor precio)', 'duracion_dias' => 365, 'precio' => 3900],
        ['id' => 7, 'nombre' => 'Mensual Plus', 'descripcion' => 'Mensual + clases dirigidas', 'duracion_dias' => 30, 'precio' => 650],
        ['id' => 8, 'nombre' => 'Anual Plus', 'descripcion' => 'Anual + clases y área premium', 'duracion_dias' => 365, 'precio' => 5200],
    ]);
}
}
