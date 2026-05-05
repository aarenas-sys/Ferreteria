<?php
use App\Models\User;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

try {
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $users = User::limit(5)->get();
    
    echo "=== Usuarios en la BD ===\n\n";
    foreach ($users as $u) {
        echo "Email: {$u->email} | Rol: {$u->role}\n";
    }
    
    if ($users->isEmpty()) {
        echo "No hay usuarios. Creando usuario de prueba...\n";
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        echo "✓ Usuario creado: admin@test.com / password\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
