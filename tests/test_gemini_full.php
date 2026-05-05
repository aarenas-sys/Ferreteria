#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Services\GeminiService;

$geminiService = new GeminiService();

$msg = "información de sucursal centro";
echo "📨 Mensaje: $msg\n\n";

try {
    $resultado = $geminiService->interpretar($msg);
    echo "📝 Respuesta RAW (completa):\n";
    echo $resultado . "\n\n";
    
    // Limpiar respuesta
    $limpio = preg_replace('/```json\s*/i', '', $resultado);
    $limpio = preg_replace('/```\s*/i', '', $limpio);
    $limpio = trim($limpio);
    
    echo "📝 Respuesta LIMPIA:\n";
    echo $limpio . "\n\n";
    
    $datos = json_decode($limpio, true);
    if ($datos) {
        echo "✅ JSON decodificado\n";
        var_dump($datos);
    } else {
        echo "❌ JSON Error: " . json_last_error_msg() . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
