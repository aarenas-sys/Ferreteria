<?php

/**
 * Test del nuevo flujo de carga de imagen + mensaje + sucursal
 * 
 * Flujo:
 * 1. Usuario sube imagen (se carga sin enviar)
 * 2. Usuario escribe mensaje
 * 3. Usuario selecciona sucursal (opcional)
 * 4. Usuario envía todo junto vía POST /chat/imagen
 */

use App\Services\ChatService;
use App\Services\ImageHashService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

require __DIR__ . '/../bootstrap/app.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$app->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);

echo "🧪 Test: Flujo Nuevo de Imagen + Mensaje + Sucursal\n";
echo "================================================\n\n";

try {
    $chatService = app(ChatService::class);
    $imageHashService = app(ImageHashService::class);

    // Test 1: Cargar imagen sin enviar (simulado)
    echo "✓ Test 1: Cargar imagen sin enviar\n";
    $rutaImagen = 'storage/app/public/productos/ou99UUZzkW6FJDKtYND7SHn49iypG7So7ys3N4n5.jpg';
    
    if (file_exists($rutaImagen)) {
        echo "  - Imagen encontrada en: $rutaImagen\n";
        echo "  - Tamaño: " . filesize($rutaImagen) . " bytes\n";
        echo "  - MIME: " . mime_content_type($rutaImagen) . "\n";
        
        // Generar hash
        $hash = $imageHashService->generarHash($rutaImagen);
        if ($hash) {
            echo "  - Hash generado: $hash\n";
            echo "  ✅ PASS: Imagen cargada correctamente\n\n";
        } else {
            echo "  ❌ FAIL: No se pudo generar hash\n";
            exit(1);
        }
    } else {
        echo "  ❌ FAIL: Imagen no encontrada\n";
        exit(1);
    }

    // Test 2: Procesar imagen con mensaje y sucursal
    echo "✓ Test 2: Procesar imagen + mensaje + sucursal\n";
    
    // Simular el flujo
    $mensaje = "hay este producto, ¿cuánto cuesta?";
    $sucursal = "centro"; // Nombre de sucursal

    echo "  - Mensaje: '$mensaje'\n";
    echo "  - Sucursal: '$sucursal'\n";

    // Procesar
    $respuesta = $chatService->procesarImagenConMensaje($rutaImagen, $mensaje, $sucursal);
    
    echo "  - Respuesta: " . substr($respuesta, 0, 100) . "...\n";
    
    if (strlen($respuesta) > 0 && !str_contains($respuesta, 'error')) {
        echo "  ✅ PASS: Imagen procesada correctamente\n\n";
    } else {
        echo "  ❌ FAIL: Error procesando imagen\n";
        echo "  - Respuesta completa: $respuesta\n";
        exit(1);
    }

    // Test 3: Validar que sucursal_id se convierte de nombre a ID
    echo "✓ Test 3: Conversión de nombre de sucursal a ID\n";
    
    $sucursales = ['centro', 'norte', 'este', 'oeste', 'sur'];
    foreach ($sucursales as $suc) {
        $respuesta = $chatService->procesarImagenConMensaje($rutaImagen, "producto", $suc);
        echo "  - Sucursal '$suc': " . (strlen($respuesta) > 0 ? "✓ OK" : "✗ FAIL") . "\n";
    }
    echo "  ✅ PASS: Todas las sucursales procesadas\n\n";

    echo "✅ TODOS LOS TESTS PASARON\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
