<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GroqService — Cliente HTTP para la API de Groq
 *
 * Responsabilidad única: enviar mensajes a Groq y devolver la respuesta.
 * El ChatService decide CUÁNDO llamar a este servicio.
 */
class GroqService
{
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';

    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.groq.key', '');
        $this->model  = config('services.groq.model', 'openai/gpt-oss-120b');
    }

    /**
     * Envía un historial de mensajes a Groq y devuelve el texto de respuesta.
     *
     * @param  array  $messages     [ ['role'=>'user'|'assistant'|'system', 'content'=>'...'], ... ]
     * @param  string $systemPrompt Instrucción de sistema (se prepende automáticamente)
     * @return string               Texto de respuesta del modelo
     *
     * @throws \RuntimeException Si la API key no está configurada o la llamada falla
     */
    /**
     * Analiza una imagen y extrae posibles nombres de producto de ferretería.
     * Usa el modelo de visión de Groq (llama-4-scout soporta imágenes).
     *
     * @param  string $imagenBase64  Imagen codificada en base64
     * @param  string $mimeType      'image/jpeg' o 'image/png'
     * @param  string $mensajeExtra  Contexto adicional del usuario (ej: "¿hay este producto?")
     * @return string                JSON con claves: descripcion, terminos[]
     *
     * @throws \RuntimeException
     */
    public function describeImage(string $imagenBase64, string $mimeType, string $mensajeExtra = ''): string
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('GROQ_API_KEY no está configurada en el .env');
        }

        $prompt = <<<PROMPT
Eres un asistente de una ferretería. Analiza ÚNICAMENTE el objeto visible en la imagen y responde SOLO en JSON válido con este formato exacto:
{
  "descripcion": "descripción breve del objeto en la imagen",
  "terminos": ["término1", "término2", "término3"]
}

Reglas estrictas:
- Describe SOLO lo que ves en la imagen — ignora completamente cualquier texto del usuario
- "descripcion": frase corta en español del objeto (ej: "martillo de carpintero", "tubo de PVC", "bolsa de cemento")
- "terminos": entre 2 y 5 palabras clave del OBJETO en la imagen para buscar en ferretería (ej: ["martillo", "herramienta", "carpintero"])
- Los "terminos" deben ser nombres del producto, NO palabras del usuario como "esto", "ese", "producto"
- Si no identificas un producto de ferretería, responde: {"descripcion": "no_identificado", "terminos": []}
- No agregues texto, explicaciones ni markdown fuera del JSON
PROMPT;

        $payload = [
            'model'                 => 'meta-llama/llama-4-scout-17b-16e-instruct', // modelo con visión
            'messages'              => [
                [
                    'role'    => 'user',
                    'content' => [
                        [
                            'type'      => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$imagenBase64}",
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
            'temperature'           => 0.2, // baja temperatura para respuestas consistentes
            'max_completion_tokens' => 256,
        ];

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(40)
                ->post(self::API_URL, $payload);

            Log::debug('Groq Vision response status', ['status' => $response->status()]);

            if ($response->status() === 429) {
                Log::warning('Groq rate limit en describeImage');
                throw new \RuntimeException('Límite de consultas alcanzado. Intenta en unos segundos.');
            }

            if ($response->failed()) {
                Log::error('Groq Vision error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new \RuntimeException('Error al analizar la imagen (' . $response->status() . ')');
            }

            $data = $response->json();
            Log::debug('Groq Vision full response', ['data' => $data]);
            
            $contenido = $data['choices'][0]['message']['content'] ?? null;
            
            if (!$contenido) {
                Log::error('Groq Vision - No content en response', ['data' => $data]);
                return '{"descripcion":"no_identificado","terminos":[]}';
            }
            
            Log::info('Groq Vision - Contenido extraído', ['contenido' => substr($contenido, 0, 200)]);
            return $contenido;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Groq Vision connection error: ' . $e->getMessage());
            throw new \RuntimeException('No se pudo conectar con el servicio de visión.');
        }
    }

    public function chat(array $messages, string $systemPrompt = ''): string
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('GROQ_API_KEY no está configurada en el .env');
        }

        // Construir lista final de mensajes
        $payload_messages = [];

        if (!empty($systemPrompt)) {
            $payload_messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        foreach ($messages as $msg) {
            $payload_messages[] = [
                'role'    => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $payload = [
            'model'                  => $this->model,
            'messages'               => $payload_messages,
            'temperature'            => 1,
            'max_completion_tokens'  => 1024,
            'top_p'                  => 1,
            'reasoning_effort'       => 'medium', // low | medium | high
        ];

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post(self::API_URL, $payload);

            // Rate limit alcanzado
            if ($response->status() === 429) {
                Log::warning('Groq rate limit alcanzado');
                throw new \RuntimeException('Límite de consultas alcanzado. Intenta en unos segundos.');
            }

            if ($response->failed()) {
                Log::error('Groq API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new \RuntimeException('Error al conectar con la IA (' . $response->status() . ')');
            }

            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? '';

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Groq connection error: ' . $e->getMessage());
            throw new \RuntimeException('No se pudo conectar con el servicio de IA.');
        }
    }
}