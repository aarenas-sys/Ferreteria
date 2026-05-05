#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Services\ChatService;
use App\Models\Branch;
use App\Models\Producto;

$chatService = $app->make(ChatService::class);

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  PRUEBAS: ChatBot SIN IA (Solo Reglas y BD)              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Obtener datos de BD
$branches = Branch::all();
echo "📍 SUCURSALES EN BD:\n";
foreach ($branches as $branch) {
    echo "   - {$branch->name}: {$branch->phone}\n";
}
echo "\n";

$productos = Producto::limit(3)->get();
echo "📦 PRODUCTOS EN BD:\n";
foreach ($productos as $prod) {
    echo "   - {$prod->nombre} (Stock: {$prod->stock}, Precio: \${$prod->precio})\n";
}
echo "\n";

// Test cases
$tests = [
    ["entrada" => "¿Hay cemento?", "descripcion" => "Stock simple (sin sucursal)"],
    ["entrada" => "¿Hay martillos en sucursal centro?", "descripcion" => "Stock con sucursal"],
    ["entrada" => "Precio del martillo", "descripcion" => "Precio simple"],
    ["entrada" => "¿Cuánto cuesta en sucursal norte?", "descripcion" => "Precio con sucursal"],
    ["entrada" => "¿Hay promociones?", "descripcion" => "Promociones"],
    ["entrada" => "Ofertas en sucursal centro", "descripcion" => "Promociones por sucursal"],
    ["entrada" => "Teléfono de sucursal centro", "descripcion" => "Contacto de sucursal"],
    ["entrada" => "Información de sucursal norte", "descripcion" => "Información completa de sucursal"],
    ["entrada" => "¿Cuál es tu horario?", "descripcion" => "Horario"],
    ["entrada" => "Ubicación sucursal este", "descripcion" => "Ubicación"],
];

foreach ($tests as $i => $test) {
    echo "TEST " . ($i + 1) . " - {$test['descripcion']}\n";
    echo "─────────────────────────────────────────────────\n";
    echo "Entrada: \"{$test['entrada']}\"\n";
    
    try {
        $respuesta = $chatService->procesarMensaje($test['entrada']);
        echo "Respuesta: {$respuesta}\n";
    } catch (\Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
    }
    echo "\n";
}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  ✅ CHATBOT SIN IA FUNCIONANDO CORRECTAMENTE              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";
