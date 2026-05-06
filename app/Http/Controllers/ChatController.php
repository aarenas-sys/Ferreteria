<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chatService) {}

    /**
     * Procesa un mensaje del usuario y devuelve una respuesta.
     * Si las reglas no reconocen la intención, ChatService delega a Groq automáticamente.
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
                'estado'    => 'exito',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'respuesta' => 'Hubo un error procesando tu mensaje. Intenta de nuevo.',
                'estado'    => 'error',
                'detalles'  => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recibe imagen + mensaje, usa Groq Vision para identificar el producto
     * y luego aplica la lógica normal de consulta (admin/sucursal).
     *
     * Validaciones:
     * - Solo JPG / PNG
     * - Máximo 10MB
     * - Archivo real (getimagesize)
     */
    public function buscarPorImagen(Request $request): JsonResponse
    {
        $request->validate([
            'imagen'  => 'required|image|mimes:jpeg,jpg,png|max:10240',
            'mensaje' => 'nullable|string|max:1000',
        ]);

        try {
            $archivo = $request->file('imagen');
            $mensaje = trim($request->input('mensaje', ''));

            if (!$archivo || !$archivo->isValid()) {
                return response()->json([
                    'respuesta' => '❌ Archivo inválido. Intenta con otra imagen.',
                    'estado'    => 'error',
                ], 400);
            }

            if (!getimagesize($archivo->getRealPath())) {
                return response()->json([
                    'respuesta' => '❌ El archivo no es una imagen válida.',
                    'estado'    => 'error',
                ], 400);
            }

            // Convertir imagen a base64 para enviar a Groq Vision
            $imagenBase64 = base64_encode(file_get_contents($archivo->getRealPath()));
            $mimeType     = $archivo->getMimeType(); // 'image/jpeg' o 'image/png'

            // Delegar a ChatService que orquesta Vision + BD + lógica admin/sucursal
            $respuesta = $this->chatService->procesarMensajeConImagen(
                $imagenBase64,
                $mimeType,
                $mensaje
            );

            return response()->json([
                'respuesta' => $respuesta,
                'estado'    => 'exito',
            ]);

        } catch (\Exception $e) {
            Log::error('Error procesando imagen: ' . $e->getMessage());
            return response()->json([
                'respuesta' => '❌ Error procesando imagen. Intenta de nuevo.',
                'estado'    => 'error',
            ], 500);
        }
    }

    /**
     * Limpia el historial de conversación de Groq (sesión).
     * Útil para que el usuario "reinicie" el contexto de la IA.
     * 
     * También limpia:
     * - Historial de Groq
     * - Producto seleccionado en chat
     * - Sucursal seleccionada
     * - Descripciones de imágenes anteriores
     * - Productos confirmados por imagen
     */
    public function clearHistory(): JsonResponse
    {
        Session::forget('groq_historial');
        Session::forget('chat_producto');
        Session::forget('chat_sucursal');
        Session::forget('imagen_descripcion');
        Session::forget('imagen_producto_confirmado');
        Session::forget('admin_mensaje_pendiente');

        Log::info('Chat history cleared for user: ' . (Auth::id() ?? 'guest'));

        return response()->json([
            'estado' => 'ok',
            'mensaje' => 'Historial limpiado correctamente'
        ]);
    }
}