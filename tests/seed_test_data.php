#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Branch;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  CREANDO DATOS DE PRUEBA                                   ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Crear sucursales faltantes
$sucursales = [
    ['name' => 'Sucursal Este', 'address' => 'Calle Este 789', 'phone' => '555-9999'],
    ['name' => 'Sucursal Oeste', 'address' => 'Avenida Oeste 321', 'phone' => '555-8888'],
];

foreach ($sucursales as $sucursal) {
    $existe = Branch::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower(explode(' ', $sucursal['name'])[1]) . '%'])->exists();
    
    if (!$existe) {
        Branch::create($sucursal);
        echo "✅ Creada sucursal: {$sucursal['name']}\n";
    } else {
        echo "⏭️  Sucursal {$sucursal['name']} ya existe\n";
    }
}

// Crear productos comunes
$productos = [
    ['codigo' => 'CEMENT001', 'nombre' => 'cemento', 'precio' => 5000, 'stock' => 50, 'sucursal_id' => 1],
    ['codigo' => 'MART001', 'nombre' => 'martillos', 'precio' => 15000, 'stock' => 30, 'sucursal_id' => 1],
    ['codigo' => 'CEMENT002', 'nombre' => 'cemento', 'precio' => 5200, 'stock' => 40, 'sucursal_id' => 2],
    ['codigo' => 'MART002', 'nombre' => 'martillos', 'precio' => 14000, 'stock' => 25, 'sucursal_id' => 2],
];

foreach ($productos as $prod) {
    $existe = Producto::whereRaw('LOWER(nombre) = ?', [strtolower($prod['nombre'])])
        ->where('sucursal_id', $prod['sucursal_id'])
        ->exists();
    
    if (!$existe) {
        Producto::create($prod);
        $suc = Branch::find($prod['sucursal_id'])->name;
        echo "✅ Creado producto: {$prod['nombre']} en {$suc}\n";
    } else {
        $suc = Branch::find($prod['sucursal_id'])->name;
        echo "⏭️  {$prod['nombre']} ya existe en {$suc}\n";
    }
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  DATOS DE PRUEBA CREADOS                                   ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";
