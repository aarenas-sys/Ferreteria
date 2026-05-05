#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Services\ChatService;

// Crear una instancia de ChatService
$chatService = new ChatService();

// Usar reflection para acceder a métodos privados
$reflection = new ReflectionClass(ChatService::class);

$detectarIntencion = $reflection->getMethod('detectarIntencion');
$detectarIntencion->setAccessible(true);

$normalizarTexto = $reflection->getMethod('normalizarTexto');
$normalizarTexto->setAccessible(true);

$testMensajes = [
    "¿Cuál es el teléfono de sucursal centro?",
    "Teléfono sucursal CENTRO",
    "Información de sucursal norte",
];

foreach ($testMensajes as $msg) {
    $normalizado = $normalizarTexto->invoke($chatService, $msg);
    $intencion = $detectarIntencion->invoke($chatService, $normalizado);
    echo "Mensaje: $msg\n";
    echo "  Normalizado: $normalizado\n";
    echo "  Intención: $intencion\n\n";
}
