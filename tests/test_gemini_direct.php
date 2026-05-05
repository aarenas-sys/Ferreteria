#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Services\ChatService;
use App\Services\GeminiService;

// Test Gemini directly
$geminiService = new GeminiService();

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  TESTING GEMINI INTERPRETATION                             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$testMensajes = [
    "información de sucursal centro",
    "hay cemento en sucursal norte",
    "precio martillo en sucursal centro",
];

foreach ($testMensajes as $msg) {
    echo "📨 Mensaje: $msg\n";
    try {
        $resultado = $geminiService->interpretar($msg);
        echo "📝 Respuesta Gemini (raw): " . substr($resultado, 0, 100) . "...\n";
        
        // Limpiar respuesta
        $limpio = preg_replace('/```json\s*/i', '', $resultado);
        $limpio = preg_replace('/```\s*/i', '', $limpio);
        $limpio = trim($limpio);
        
        $datos = json_decode($limpio, true);
        if ($datos) {
            echo "✅ JSON decodificado exitosamente\n";
            echo "   Intent: " . ($datos['intent'] ?? 'N/A') . "\n";
            echo "   Producto: " . ($datos['producto'] ?? 'N/A') . "\n";
            echo "   Sucursal: " . ($datos['sucursal'] ?? 'N/A') . "\n";
        } else {
            echo "❌ JSON decode falló\n";
            echo "   JSON Error: " . json_last_error_msg() . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
