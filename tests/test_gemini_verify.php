#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Services\GeminiService;

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICAR GEMINI CON gemini-1.5-flash                    ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Verificar API key
$apiKey = config('services.gemini.key');
echo "🔑 API Key: " . substr($apiKey, 0, 10) . "...\n";
echo "🔍 Verificar que sea: AIzaSyCHBRt4...\n\n";

$geminiService = new GeminiService();

$mensaje = "hay cemento en sucursal centro";
echo "📨 Enviando mensaje: '$mensaje'\n";
echo "⏳ Esperando respuesta...\n\n";

try {
    $resultado = $geminiService->interpretar($mensaje);
    
    if ($resultado === null) {
        echo "❌ GeminiService retornó NULL\n";
        echo "   Revisa storage/logs/laravel.log\n";
    } else {
        echo "✅ Gemini respondió\n\n";
        echo "📝 Respuesta raw:\n";
        echo $resultado . "\n\n";
        
        $datos = json_decode($resultado, true);
        if ($datos) {
            echo "✅ JSON válido\n";
            echo "   Intent: " . ($datos['intent'] ?? 'N/A') . "\n";
            echo "   Producto: " . ($datos['producto'] ?? 'N/A') . "\n";
            echo "   Sucursal: " . ($datos['sucursal'] ?? 'N/A') . "\n";
        } else {
            echo "❌ JSON inválido: " . json_last_error_msg() . "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n";
