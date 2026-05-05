<?php
require 'vendor/autoload.php';

// Crear la app
$app = require 'bootstrap/app.php';

// Test del servicio ChatService directamente
$chatService = $app->make(App\Services\ChatService::class);

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST HTTP ENDPOINT /chat (Simulado)                      ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$testCases = [
    ['mensaje' => '¿Hay cemento?', 'descripcion' => 'Stock simple'],
    ['mensaje' => '¿Hay martillos en sucursal centro?', 'descripcion' => 'Stock con sucursal'],
    ['mensaje' => 'Precio del martillo', 'descripcion' => 'Precio simple'],
    ['mensaje' => 'Teléfono de sucursal norte', 'descripcion' => 'Contacto de sucursal'],
    ['mensaje' => '¿Hay promociones?', 'descripcion' => 'Promociones'],
    ['mensaje' => 'Horario', 'descripcion' => 'Horario'],
];

foreach ($testCases as $test) {
    $mensaje = $test['mensaje'];
    $descripcion = $test['descripcion'];
    
    try {
        $respuesta = $chatService->procesarMensaje($mensaje);
        
        echo "📝 TEST: $descripcion\n";
        echo "   Entrada: \"$mensaje\"\n";
        echo "   Respuesta: $respuesta\n";
        echo "\n";
    } catch (\Exception $e) {
        echo "❌ ERROR en $descripcion\n";
        echo "   " . $e->getMessage() . "\n\n";
    }
}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  ✅ TODOS LOS TESTS COMPLETADOS                           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
