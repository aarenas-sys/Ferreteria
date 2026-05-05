<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChatService;
use App\Services\ImageHashService;

class TestImageWorkflow extends Command
{
    protected $signature = 'test:image-workflow';
    protected $description = 'Test nuevo flujo de imagen + mensaje + sucursal';

    public function handle(ChatService $chatService, ImageHashService $imageHashService)
    {
        $this->info('🧪 Validando nuevo flujo de imagen + mensaje + sucursal');
        $this->line('==================================================');
        $this->newLine();

        // Buscar una imagen
        $rutaImagen = 'storage/app/public/productos/ou99UUZzkW6FJDKtYND7SHn49iypG7So7ys3N4n5.jpg';
        
        if (!file_exists($rutaImagen)) {
            $this->warn("Imagen no encontrada en: $rutaImagen");
            $files = glob('storage/app/public/productos/*.jpg');
            if (count($files) > 0) {
                $rutaImagen = $files[0];
                $this->info("✓ Usando: " . basename($rutaImagen));
            } else {
                $this->error("No se encontraron imágenes");
                return 1;
            }
        }

        $this->newLine();
        $this->info('Test 1: Generando hash de imagen');
        $hash = $imageHashService->generarHash($rutaImagen);
        if ($hash) {
            $this->line("✅ Hash: $hash");
        } else {
            $this->error("❌ No se pudo generar hash");
            return 1;
        }

        $this->newLine();
        $this->info('Test 2: Procesando imagen + mensaje + sucursal');
        $respuesta = $chatService->procesarImagenConMensaje(
            $rutaImagen,
            "hay este producto, ¿cuánto cuesta?",
            "centro"
        );
        
        if (strlen($respuesta) > 0) {
            $this->line("✅ Respuesta obtenida:");
            $this->line("   " . substr($respuesta, 0, 150) . "...");
        } else {
            $this->error("❌ Respuesta vacía");
            return 1;
        }

        $this->newLine();
        $this->info('Test 3: Validando todas las sucursales');
        $sucursales = ['centro', 'norte', 'este', 'oeste', 'sur', null];
        foreach ($sucursales as $suc) {
            $respuesta = $chatService->procesarImagenConMensaje($rutaImagen, "producto", $suc);
            $sucName = $suc ?? 'sin sucursal';
            $status = strlen($respuesta) > 0 ? '✅' : '❌';
            $this->line("   - " . str_pad($sucName, 15) . ": $status");
        }

        $this->newLine();
        $this->info('✅ TODOS LOS TESTS COMPLETADOS');
        return 0;
    }
}
