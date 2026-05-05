#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Services\ChatService;
use App\Models\Branch;

$chatService = $app->make(ChatService::class);

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  PRUEBA DE MEJORAS: Extracción y Búsqueda por Sucursal    ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Obtener sucursales disponibles
$branches = Branch::all();
echo "📍 SUCURSALES DISPONIBLES EN BD:\n";
foreach ($branches as $branch) {
    echo "   - {$branch->name}: {$branch->phone}\n";
}
echo "\n";

// Test 1: Consulta normal de stock
echo "TEST 1 - Stock (regla, sin Gemini):\n";
echo "───────────────────────────────────\n";
$r1 = $chatService->procesarMensaje("¿Hay cemento?");
echo "Entrada: '¿Hay cemento?'\n";
echo "Respuesta: $r1\n\n";

// Test 2: Stock con sucursal específica
echo "TEST 2 - Stock con sucursal por REGLAS:\n";
echo "────────────────────────────────────────\n";
$r2 = $chatService->procesarMensaje("¿Hay cemento en sucursal centro?");
echo "Entrada: '¿Hay cemento en sucursal centro?'\n";
echo "Respuesta: $r2\n\n";

// Test 3: Teléfono de sucursal
echo "TEST 3 - Teléfono de sucursal (debería ir a Gemini):\n";
echo "──────────────────────────────────────────────────────\n";
$r3 = $chatService->procesarMensaje("¿Cuál es el teléfono de sucursal centro?");
echo "Entrada: '¿Cuál es el teléfono de sucursal centro?'\n";
echo "Respuesta: $r3\n\n";

// Test 4: Producto con sucursal incorrecta
echo "TEST 4 - Producto en sucursal inexistente:\n";
echo "──────────────────────────────────────────\n";
$r4 = $chatService->procesarMensaje("¿Hay martillos en sucursal xyz?");
echo "Entrada: '¿Hay martillos en sucursal xyz?'\n";
echo "Respuesta: $r4\n\n";

// Test 5: Case insensitive
echo "TEST 5 - Case insensitive (CENTRO vs centro):\n";
echo "─────────────────────────────────────────────\n";
$r5 = $chatService->procesarMensaje("Teléfono sucursal CENTRO");
echo "Entrada: 'Teléfono sucursal CENTRO'\n";
echo "Respuesta: $r5\n\n";

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  FIN DE PRUEBAS                                            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";
