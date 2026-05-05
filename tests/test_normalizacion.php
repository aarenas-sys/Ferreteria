#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Services\ChatService;

// Crear una instancia de ChatService
$reflection = new ReflectionClass(ChatService::class);

// Usar reflection para acceder a los métodos privados
$normalizarTexto = $reflection->getMethod('normalizarTexto');
$normalizarTexto->setAccessible(true);

$mensaje = "¿Cuál es el teléfono de sucursal centro?";
$normalizado = $normalizarTexto->invoke(new ChatService(), $mensaje);

echo "Original: $mensaje\n";
echo "Normalizado: $normalizado\n";
echo "Contiene 'sucursal': " . (strpos($normalizado, 'sucursal') !== false ? "SÍ" : "NO") . "\n";
echo "Contiene 'telefono': " . (strpos($normalizado, 'telefono') !== false ? "SÍ" : "NO") . "\n";
