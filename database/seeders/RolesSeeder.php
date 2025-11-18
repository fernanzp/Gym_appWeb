<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Importante para que funcione DB::

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Inserta los datos de tu archivo SQL
        DB::table('roles')->insert([
            ['id' => 1, 'rol' => 'admin'],
            ['id' => 2, 'rol' => 'staff'],
            ['id' => 3, 'rol' => 'member']
        ]);
    }
}