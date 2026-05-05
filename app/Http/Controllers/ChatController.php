<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Procesa un mensaje del usuario y devuelve una respuesta
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'mensaje' => 'required|string|max:1000',
        ]);

        $mensaje = $request->input('mensaje');
        
        try {
            $respuesta = $this->chatService->procesarMensaje($mensaje);
            
            return response()->json([
                'respuesta' => $respuesta,
                'estado' => 'exito'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'respuesta' => 'Hubo un error procesando tu mensaje. Intenta de nuevo.',
                'estado' => 'error',
                'detalles' => $e->getMessage() // Solo en desarrollo
            ], 500);
        }
    }

    /**
     * Valida y procesa la imagen junto con el mensaje
     * 
     * Validaciones:
     * - Una sola imagen
     * - Solo JPG, PNG
     * - Máximo 10MB
     * 
     * Flujo:
     * 1. Usuario sube imagen
     * 2. Usuario escribe mensaje
     * 3. Envía ambos
     * 4. Backend responde basado SOLO en el mensaje (sin procesar imagen)
     */
    public function buscarPorImagen(Request $request): JsonResponse
    {
        $request->validate([
            'imagen' => 'required|image|mimes:jpeg,jpg,png|max:10240',
            'mensaje' => 'nullable|string|max:1000',
        ]);

        try {
            $archivo = $request->file('imagen');
            $mensaje = $request->input('mensaje', '');

            // Validar que es un archivo válido
            if (!$archivo || !$archivo->isValid()) {
                return response()->json([
                    'respuesta' => '❌ Archivo inválido. Intenta con otra imagen.',
                    'estado' => 'error'
                ], 400);
            }

            // Validar que es una imagen de verdad (no solo extensión falsa)
            $rutaTmp = $archivo->getRealPath();
            if (!getimagesize($rutaTmp)) {
                return response()->json([
                    'respuesta' => '❌ El archivo no es una imagen válida. Verifica que no esté corrupto.',
                    'estado' => 'error'
                ], 400);
            }

            // Si hay mensaje, procesarlo normalmente
            if ($mensaje) {
                $respuesta = $this->chatService->procesarMensaje($mensaje);
                return response()->json([
                    'respuesta' => $respuesta,
                    'estado' => 'exito'
                ]);
            }

            // Si NO hay mensaje, solo confirmar que se cargó la imagen
            return response()->json([
                'respuesta' => '✔ Imagen cargada - escribe el nombre del producto',
                'estado' => 'exito'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error procesando imagen: ' . $e->getMessage());
            return response()->json([
                'respuesta' => '❌ Error procesando imagen. Intenta con otra o reinicia el chat.',
                'estado' => 'error'
            ], 500);
        }
    }
}
