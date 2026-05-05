#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$apiKey = env('GEMINI_API_KEY');
$mensaje = "información de sucursal centro";

echo "API Key: " . substr($apiKey, 0, 10) . "...\n\n";

$response = Http::timeout(30)->post(
    "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key={$apiKey}",
    [
        "contents" => [
            [
                "parts" => [
                    [
                        "text" => "Extrae información: $mensaje. Responde SOLO JSON."
                    ]
                ]
            ]
        ]
    ]
);

echo "Status: " . $response->status() . "\n";
echo "Full Response:\n";
var_dump($response->json());
echo "\n\nRaw Body:\n";
echo $response->body();
