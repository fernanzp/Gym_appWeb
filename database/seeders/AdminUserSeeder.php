<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buscamos el ID del rol 'admin' (equivalente a tu subquery)
        // Si no existe, podrías optar por crearlo o lanzar un error.
        $rolId = DB::table('roles')->where('rol', 'admin')->value('id');

        if (!$rolId) {
            $this->command->error('El rol "admin" no existe en la tabla roles. Crea el rol primero.');
            return;
        }

        // 2. Insertamos el usuario y capturamos su ID automáticamente
        // Usamos insertGetId para obtener el ID del nuevo usuario inmediatamente
        $usuarioId = DB::table('usuarios')->insertGetId([
            'nombre_comp' => 'Administrador',
            'email'       => 'admin@gmail.com',
            'telefono'    => '1234567890',
            'fecha_nac'   => '1980-01-01',
            // Nota: He dejado tu hash original.
            // Si quisieras cambiar la contraseña en el futuro usa: Hash::make('tu_password')
            'contrasena'  => '$2y$10$J88PDw7FubUKVumhXKYiXezuDMPi9q6kARp/GYcpq.8tl1w4ZUghK',
            'estatus'     => 1,
            // created_at y updated_at suelen ser necesarios en Laravel,
            // descomenta las siguientes líneas si tus tablas los usan:
            // 'created_at' => now(),
            // 'updated_at' => now(),
        ]);

        // 3. Insertamos la relación en la tabla pivote
        DB::table('roles_usuarios')->insert([
            'usuario_id' => $usuarioId,
            'rol_id'     => $rolId,
        ]);
        
        $this->command->info('Usuario Administrador creado y rol asignado correctamente.');
    }
}