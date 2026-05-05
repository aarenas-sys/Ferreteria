<?php

// Test simple del nuevo flujo
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "🧪 Validando nuevo flujo de imagen + mensaje + sucursal\n";
echo "==================================================\n\n";

try {
    $chatService = app(\App\Services\ChatService::class);
    $imageHashService = app(\App\Services\ImageHashService::class);

    // Buscar una imagen de test
    $rutaImagen = 'storage/app/public/productos/ou99UUZzkW6FJDKtYND7SHn49iypG7So7ys3N4n5.jpg';
    
    if (!file_exists($rutaImagen)) {
        echo "⚠️  Imagen de test no encontrada en: $rutaImagen\n";
        echo "Buscando alternativas...\n";
        $files = glob('storage/app/public/productos/*.jpg');
        if (count($files) > 0) {
            $rutaImagen = $files[0];
            echo "✓ Usando: " . basename($rutaImagen) . "\n\n";
        } else {
            echo "❌ No se encontraron imágenes JPG\n";
            exit(1);
        }
    }

    // Test 1: Generar hash
    echo "Test 1: Generando hash de imagen\n";
    $hash = $imageHashService->generarHash($rutaImagen);
    if ($hash) {
        echo "✅ Hash: $hash\n\n";
    } else {
        echo "❌ No se pudo generar hash\n";
        exit(1);
    }

    // Test 2: Procesar imagen + mensaje + sucursal
    echo "Test 2: Procesando imagen + mensaje + sucursal\n";
    $respuesta = $chatService->procesarImagenConMensaje(
        $rutaImagen,
        "hay este producto, ¿cuánto cuesta?",
        "centro"
    );
    
    if (strlen($respuesta) > 0) {
        echo "✅ Respuesta obtenida (primeros 150 caracteres):\n";
        echo "   " . substr($respuesta, 0, 150) . "\n\n";
    } else {
        echo "❌ Respuesta vacía\n";
        exit(1);
    }

    // Test 3: Validar sucursales
    echo "Test 3: Validando todas las sucursales\n";
    $sucursales = ['centro', 'norte', 'este', 'oeste', 'sur', null];
    foreach ($sucursales as $suc) {
        $respuesta = $chatService->procesarImagenConMensaje($rutaImagen, "producto", $suc);
        $sucName = $suc ?? 'sin sucursal';
        echo "   - " . str_pad($sucName, 15) . ": " . (strlen($respuesta) > 0 ? "✅" : "❌") . "\n";
    }

    echo "\n✅ TODOS LOS TESTS COMPLETADOS\n";

} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
