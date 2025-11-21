<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;

class VisitaUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creamos el usuario "Fantasma" para las visitas
        // Usamos el ID 99999 para que sea fácil de identificar y no estorbe
        Usuario::updateOrCreate(
            ['id' => 99999], // Buscamos por este ID
            [
                'nombre_comp'    => 'VISITA CASUAL',
                'email'          => 'visita@gymflow.local', // Email dummy
                'telefono'       => '0000000000',
                'fecha_nac'      => null,
                'contrasena'     => null, // No necesita login
                'estatus'        => 1,    // Activo para que el sistema no lo bloquee
                'fingerprint_id' => null, // IMPORTANTE: Sin huella
                'is_inside'      => 0,    // Por defecto fuera
            ]
        );
        
        $this->command->info('✅ Usuario de Visitas (ID: 99999) creado o actualizado correctamente.');
    }
}
