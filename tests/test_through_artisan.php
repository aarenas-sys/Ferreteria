<?php
// test_through_artisan.php
use App\Services\ChatService;

// This will be run through artisan
$chatService = app(ChatService::class);

echo "✅ PRUEBAS FINALES - CHATBOT SIN IA\n";
echo "════════════════════════════════════════════\n\n";

$tests = [
    ['msg' => '¿Hay cemento?', 'desc' => 'Stock simple'],
    ['msg' => '¿Hay martillos en sucursal centro?', 'desc' => 'Stock con sucursal'],
    ['msg' => 'Precio del martillo', 'desc' => 'Precio simple'],
    ['msg' => 'Teléfono de sucursal norte', 'desc' => 'Contacto sucursal'],
    ['msg' => '¿Hay promociones?', 'desc' => 'Promociones'],
    ['msg' => 'Horario de atención', 'desc' => 'Horario'],
];

foreach ($tests as $test) {
    try {
        $resp = $chatService->procesarMensaje($test['msg']);
        echo "📝 " . $test['desc'] . "\n";
        echo "   Q: {$test['msg']}\n";
        echo "   A: {$resp}\n\n";
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n\n";
    }
}

echo "════════════════════════════════════════════\n";
echo "✅ TODAS LAS PRUEBAS COMPLETADAS\n";
