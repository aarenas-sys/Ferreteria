#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$apiKey = config('services.gemini.key');

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  PROBANDO MODELOS DISPONIBLES                              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$modelos = [
    'gemini-pro',
    'gemini-1.5-flash',
    'gemini-2.0-flash',
    'gemini-2.0-flash-001',
    'gemini-1.5-pro',
    'gemini-pro-vision'
];

foreach ($modelos as $modelo) {
    echo "🔄 Probando: $modelo ... ";
    
    try {
        $response = Http::timeout(5)->post(
            "https://generativelanguage.googleapis.com/v1/models/{$modelo}:generateContent?key={$apiKey}",
            [
                "contents" => [
                    [
                        "parts" => [
                            [
                                "text" => "Hola"
                            ]
                        ]
                    ]
                ]
            ]
        );
        
        $status = $response->status();
        
        if ($status === 200) {
            echo "✅ FUNCIONA\n";
        } elseif ($status === 404) {
            echo "❌ No disponible (404)\n";
        } elseif ($status === 429) {
            echo "⚠️  Sin cuota (429)\n";
        } else {
            echo "❓ Status: $status\n";
        }
    } catch (\Exception $e) {
        echo "❌ Error: " . substr($e->getMessage(), 0, 40) . "...\n";
    }
}

echo "\n";
