<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
        PlanesSeeder::class,
        RolesSeeder::class,
        AdminUserSeeder::class,
        // Aquí puedes agregar más seeders en el futuro
    
    
    ]);
        // User::factory(10)->create();

       // User::factory()->create([
          //  'name' => 'Test User',
        //    'email' => 'test@example.com',
      //  ]);
    }
}
