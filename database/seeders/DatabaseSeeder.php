<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@example.com',
            'role' => 'supervisor',
        ]);

        User::factory()->create([
            'name' => 'Bodeguero User',
            'email' => 'bodeguero@example.com',
            'role' => 'bodeguero',
        ]);

        User::factory()->create([
            'name' => 'Cajero User',
            'email' => 'cajero@example.com',
            'role' => 'cajero',
        ]);

        // Crear sucursales de ejemplo
        $branch1 = \App\Models\Branch::create([
            'name' => 'Sucursal Centro',
            'address' => 'Calle Principal 123',
            'phone' => '555-1234',
        ]);

        $branch2 = \App\Models\Branch::create([
            'name' => 'Sucursal Norte',
            'address' => 'Avenida Norte 456',
            'phone' => '555-5678',
        ]);

        // Asignar sucursales a usuarios
        User::where('email', 'supervisor@example.com')->update(['branch_id' => $branch1->id]);
        User::where('email', 'bodeguero@example.com')->update(['branch_id' => $branch1->id]);
        User::where('email', 'cajero@example.com')->update(['branch_id' => $branch2->id]);

        // Configuraciones iniciales
        \App\Models\Setting::create(['key' => 'iva', 'value' => '19']);
        \App\Models\Setting::create(['key' => 'discount', 'value' => '5']);
    }
}
