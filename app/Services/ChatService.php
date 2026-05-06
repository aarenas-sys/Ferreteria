<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Branch;
use App\Models\Discount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

/**
 * ChatService — Chatbot híbrido: reglas + IA (Groq como fallback)
 *
 * Flujo de decisión:
 *  1. Se normaliza el mensaje
 *  2. Se detecta la intención por palabras clave (lógica original intacta)
 *  3. Si la intención es conocida → responde con reglas (BD)
 *  4. Si la intención es 'desconocida' → delega a Groq para respuesta contextual
 */
class ChatService
{
    // ─── Dependencia IA ────────────────────────────────────────────────────────

    public function __construct(private readonly GroqService $groq) {}

    // ─── Palabras clave (sin cambios respecto al original) ─────────────────────

    private array $palabrasClaveStock        = ['stock', 'disponible', 'hay', 'tenemos', 'quedan', 'cantidad', 'tiene'];
    private array $palabrasClavePrice        = ['precio', 'cuesta', 'vale', 'costo', 'cuanto'];
    private array $palabrasClavePromo        = ['promocion', 'descuento', 'rebaja', 'oferta'];
    private array $palabrasClaveHorario      = ['horario', 'hora', 'abierto', 'cierra'];
    private array $palabrasClaveUbicacion    = ['ubicacion', 'direccion', 'donde', 'localizacion'];
    private array $palabrasClaveContacto     = ['contacto', 'telefono', 'whatsapp', 'llamar', 'llamada', 'email', 'correo'];
    private array $palabrasClaveInfoSucursal = ['sucursal', 'sucursales', 'branch', 'branches'];

    private array $palabrasAEliminar = [
        'precio', 'hay', 'en', 'la', 'el', 'de', 'sucursal', 'del', 'las', 'los',
        'un', 'una', 'unos', 'unas', 'es', 'son', 'que', 'cuanto', 'cuantos',
        'cuanta', 'cuantas', 'cuesta', 'vale', 'disponible', 'stock', 'tenemos'
    ];

    private array $sucursalesConocidas = ['norte', 'sur', 'centro', 'este', 'oeste'];

    // ─── System prompt para Groq ────────────────────────────────────────────────

    /**
     * Instrucción de sistema que define el rol de Groq dentro de FerreNet.
     * Se inyecta contexto de BD para que la IA tenga información real.
     */
    private function buildSystemPrompt(): string
    {
        // Recopilar info básica de sucursales para darle contexto a la IA
        $sucursales = Branch::select('name', 'address', 'phone')->get();
        $sucursalInfo = $sucursales->map(fn($b) => "{$b->name} — {$b->address} — Tel: {$b->phone}")->implode("\n");

        return <<<PROMPT
Eres el asistente virtual de FerreNet, una ferretería con múltiples sucursales.
Responde siempre en español, de forma amable, clara y concisa.
Usa emojis ocasionalmente para hacer la conversación más amigable.
NO inventes precios ni stock — si no tienes datos exactos, orienta al usuario a preguntar de forma más específica.

Sucursales disponibles:
{$sucursalInfo}

Horario general: Lunes a Viernes 8:00 AM - 5:00 PM, Sábados y Domingos cerrado.

Si el usuario pregunta algo que no puedes responder con seguridad (como stock exacto o precio de un producto),
pídele que reformule su pregunta usando frases como "¿Hay [producto]?" o "¿Precio del [producto]?".
PROMPT;
    }

    // ─── Entrada pública para búsqueda por imagen ──────────────────────────────

    /**
     * Procesa una imagen subida por el usuario:
     * 1. Groq Vision describe la imagen y extrae términos de búsqueda
     * 2. Se buscan coincidencias en BD por nombre y categoría
     * 3. Si hay varias → lista para que el usuario elija
     * 4. Si hay exactamente una → procesa directamente con la lógica normal
     * 5. Si no hay → pide que escriba el nombre manualmente
     *
     * La lógica de admin/sucursal se aplica al momento de consultar stock/precio.
     */
    public function procesarMensajeConImagen(string $imagenBase64, string $mimeType, string $mensajeUsuario = ''): string
    {
        // Paso 1: Groq Vision analiza la imagen
        try {
            $jsonRespuesta = $this->groq->describeImage($imagenBase64, $mimeType, $mensajeUsuario);
            
            // DEBUG Y VALIDACIÓN
            Log::info('GroqVision - Respuesta RAW: ' . substr($jsonRespuesta, 0, 200));
            
            // Validar que la respuesta sea JSON válido
            if (empty($jsonRespuesta) || !json_decode($jsonRespuesta)) {
                Log::error('GroqVision - JSON inválido o vacío: ' . $jsonRespuesta);
                return "⚠️ Error: La respuesta de visión no es válida. Por favor, escribe el nombre del producto directamente.";
            }
        } catch (\RuntimeException $e) {
            Log::warning('GroqVision falló: ' . $e->getMessage());
            return "⚠️ No pude analizar la imagen. Por favor escribe el nombre del producto directamente.";
        }

        // Paso 2: Parsear JSON de Groq
        $datos = json_decode($jsonRespuesta, true);

        // Validar estructura del JSON
        if (!is_array($datos)) {
            Log::error('GroqVision - Datos no son array: ' . $jsonRespuesta);
            return "⚠️ Error procesando la imagen. Por favor escribe el nombre del producto.";
        }

        if (!$datos || ($datos['descripcion'] ?? '') === 'no_identificado' || empty($datos['terminos'])) {
            Log::info('GroqVision - Producto no identificado', [
                'descripcion' => $datos['descripcion'] ?? 'N/A',
                'terminos' => $datos['terminos'] ?? []
            ]);
            return "🤔 No identifiqué un producto de ferretería en la imagen.\n\n¿Puedes escribir el nombre del producto que buscas?";
        }

        $descripcion = $datos['descripcion'];
        $terminos    = $datos['terminos'];

        Log::info('GroqVision - Producto identificado', [
            'descripcion' => $descripcion,
            'terminos' => $terminos
        ]);

        // Paso 3: Buscar en BD por nombre y categoría usando los términos
        $productos = $this->buscarProductosPorTerminos($terminos);

        if ($productos->isEmpty()) {
            Session::put('imagen_descripcion', $descripcion);
            Log::info('GroqVision - No encontrados productos similares', [
                'terminos' => $terminos,
                'descripcion' => $descripcion
            ]);
            return "🔍 La imagen parece ser **{$descripcion}**, pero no encontré productos similares en el catálogo.\n\n¿Puedes escribir el nombre exacto del producto?";
        }

        // Paso 4: Una sola coincidencia → procesar directamente
        if ($productos->count() === 1) {
            $producto = $productos->first();
            Session::put('imagen_producto_confirmado', $producto->nombre);
            Log::info('GroqVision - Una coincidencia encontrada', [
                'producto_id' => $producto->id,
                'producto_nombre' => $producto->nombre
            ]);
            // Construir mensaje como si el usuario lo hubiera escrito
            return $this->procesarMensaje("¿Hay {$producto->nombre}?");
        }

        // Paso 5: Varias coincidencias → mostrar lista con botones para elegir
        Session::put('imagen_descripcion', $descripcion);

        Log::info('GroqVision - Múltiples coincidencias', [
            'cantidad' => $productos->count(),
            'productos' => $productos->pluck('nombre')->toArray()
        ]);

        // Crear opciones únicas por nombre para evitar duplicados en UI
        $opcionesUnicas = [];
        $nombresVisto = [];
        
        foreach ($productos as $p) {
            if (!in_array($p->nombre, $nombresVisto)) {
                $opcionesUnicas[] = [
                    'label' => $p->nombre,
                    'value' => '¿Hay ' . $p->nombre . '?',
                ];
                $nombresVisto[] = $p->nombre;
            }
        }

        $opcionesJson = json_encode($opcionesUnicas, JSON_UNESCAPED_UNICODE);

        return "🔍 La imagen parece ser **{$descripcion}**. Encontré estos productos similares:\n\n__OPCIONES__{$opcionesJson}\n\n_¿No es ninguno? Escribe el nombre exacto._";
    }

    /**
     * Busca productos en BD usando una lista de términos.
     * Busca en nombre del producto Y en nombre de categoría.
     * Retorna máximo 6 resultados para no abrumar al usuario.
     */
    private function buscarProductosPorTerminos(array $terminos)
    {
        return Producto::query()
            ->where(function ($query) use ($terminos) {
                foreach ($terminos as $termino) {
                    $t = strtolower(trim($termino));
                    $query->orWhereRaw('LOWER(nombre) LIKE ?', ["%{$t}%"]);
                }
            })
            ->orWhereHas('categoria', function ($query) use ($terminos) {
                $query->where(function ($q) use ($terminos) {
                    foreach ($terminos as $termino) {
                        $t = strtolower(trim($termino));
                        $q->orWhereRaw('LOWER(nombre) LIKE ?', ["%{$t}%"]);
                    }
                });
            })
            ->select('id', 'nombre', 'precio', 'stock', 'categoria_id')
            ->distinct()
            ->limit(6)
            ->get();
    }

    // ─── Punto de entrada principal ─────────────────────────────────────────────

    /**
     * Procesa el mensaje del usuario.
     * Mantiene la lógica original de reglas y agrega Groq como fallback.
     */
    /**
     * Intenciones que tienen sentido filtrar por sucursal.
     * Horario y promociones generales NO están aquí porque pueden responderse globalmente.
     */
    private array $intencionesConSucursal = ['stock', 'precio', 'ubicacion', 'contacto', 'info_sucursal'];

    public function procesarMensaje(string $mensaje): string
    {
        $mensajeNorm = $this->normalizarTexto($mensaje);
        $intencion   = $this->detectarIntencion($mensajeNorm);
        $sucursal    = $this->detectarSucursal($mensajeNorm);

        // ── Manejo especial: Admin selecciona sucursal ────────────────────────
        // Si el mensaje es "sucursal_X", recuperar el mensaje pendiente
        if (Auth::check() && Auth::user()->role === 'admin') {
            if (preg_match('/^sucursal_(.+)$/i', $mensaje, $matches)) {
                $nombreSucursal = ucfirst(strtolower($matches[1]));
                $sucursal = strtolower($nombreSucursal);
                
                // Recuperar el mensaje pendiente (ej: "¿Hay martillos?")
                $mensajePendiente = Session::get('admin_mensaje_pendiente');
                
                if ($mensajePendiente) {
                    Session::forget('admin_mensaje_pendiente');
                    // Reprocessar el mensaje pendiente CON la sucursal elegida
                    $mensajeConSucursal = "{$mensajePendiente} {$nombreSucursal}";
                    return $this->procesarMensaje($mensajeConSucursal);
                }
            }
        }

        // ── Lógica según rol del usuario ─────────────────────────────────────
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'admin') {
                // Admin sin sucursal especificada en una intención que la requiere
                // → pedirle que elija sucursal antes de continuar
                if (!$sucursal && in_array($intencion, $this->intencionesConSucursal)) {
                    // Guardar el mensaje original en sesión para reusarlo tras elegir sucursal
                    Session::put('admin_mensaje_pendiente', $mensaje);
                    return $this->pedirSucursalAlAdmin($intencion);
                }
            } else {
                // Usuario normal: asignar su sucursal automáticamente si no especificó
                if (!$sucursal && isset($user->sucursal_id) && $user->sucursal_id) {
                    $sucursal = $this->obtenerNombreSucursal($user->sucursal_id);
                }
            }
        }

        // ── Lógica de reglas (intacta) ────────────────────────────────────────
        if ($intencion !== 'desconocida') {
            return match ($intencion) {
                'stock'         => $this->consultarStock($mensajeNorm, $sucursal),
                'precio'        => $this->consultarPrecio($mensajeNorm, $sucursal),
                'promocion'     => $this->consultarPromociones($sucursal),
                'horario'       => $this->consultarHorario(),
                'ubicacion'     => $this->consultarUbicacion($sucursal),
                'contacto'      => $this->consultarContacto($sucursal),
                'info_sucursal' => $this->consultarInfoSucursal($sucursal),
                default         => $this->respuestaNoEntendida(),
            };
        }

        // ── Fallback: delegar a Groq ──────────────────────────────────────────
        return $this->responderConGroq($mensaje);
    }

    /**
     * Genera el mensaje que le pide al admin elegir una sucursal.
     * Incluye las sucursales reales de la BD como opciones con prefijo "sucursal_".
     * El frontend detecta ese prefijo para renderizar botones en lugar de texto.
     */
    private function pedirSucursalAlAdmin(string $intencion): string
    {
        $sucursales = Branch::select('id', 'name')->orderBy('name')->get();

        $accion = match ($intencion) {
            'stock'         => 'consultar stock',
            'precio'        => 'consultar precios',
            'ubicacion'     => 'ver la ubicación',
            'contacto'      => 'ver el contacto',
            'info_sucursal' => 'ver información',
            default         => 'consultar',
        };

        // Serializar opciones como JSON para que el frontend las convierta en botones
        $opciones = $sucursales->map(fn($b) => [
            'label'  => $b->name,
            'value'  => 'sucursal_' . strtolower($b->name),
        ])->values()->toArray();

        // Formato especial que el frontend detecta: __OPCIONES__[{...}]
        $opcionesJson = json_encode($opciones, JSON_UNESCAPED_UNICODE);

        return "🏢 ¿De cuál sucursal deseas {$accion}?\n\n__OPCIONES__{$opcionesJson}";
    }

    // ─── Fallback IA ────────────────────────────────────────────────────────────

    /**
     * Llama a Groq cuando las reglas no reconocen la intención.
     * Mantiene historial de conversación en sesión (máx. 10 turnos = 20 mensajes).
     */
    private function responderConGroq(string $mensajeOriginal): string
    {
        // Recuperar historial de la sesión
        $historial = Session::get('groq_historial', []);

        // Agregar mensaje actual del usuario al historial
        $historial[] = ['role' => 'user', 'content' => $mensajeOriginal];

        try {
            $respuesta = $this->groq->chat($historial, $this->buildSystemPrompt());

            // Guardar respuesta de la IA en el historial
            $historial[] = ['role' => 'assistant', 'content' => $respuesta];

            // Mantener solo los últimos 20 mensajes (10 turnos) para no exceder tokens
            if (count($historial) > 20) {
                $historial = array_slice($historial, -20);
            }

            Session::put('groq_historial', $historial);

            return $respuesta;

        } catch (\RuntimeException $e) {
            // Si Groq falla, mostrar mensaje de fallback amigable
            Log::warning('Groq fallback falló: ' . $e->getMessage());
            return $this->respuestaNoEntendida();
        }
    }

    // ─── Detección de intención (sin cambios) ──────────────────────────────────

    private function detectarIntencion(string $mensaje): string
    {
        if ($this->coincideAlgunasPalabras($mensaje, $this->palabrasClavePromo))        return 'promocion';
        if ($this->coincideAlgunasPalabras($mensaje, $this->palabrasClaveStock))        return 'stock';
        if ($this->coincideAlgunasPalabras($mensaje, $this->palabrasClavePrice))        return 'precio';
        if ($this->coincideAlgunasPalabras($mensaje, $this->palabrasClaveInfoSucursal)) return 'info_sucursal';
        if ($this->coincideAlgunasPalabras($mensaje, $this->palabrasClaveHorario))      return 'horario';
        if ($this->coincideAlgunasPalabras($mensaje, $this->palabrasClaveUbicacion))    return 'ubicacion';
        if ($this->coincideAlgunasPalabras($mensaje, $this->palabrasClaveContacto))     return 'contacto';
        return 'desconocida';
    }

    private function coincideAlgunasPalabras(string $mensaje, array $palabras): bool
    {
        foreach ($palabras as $palabra) {
            if (str_contains($mensaje, $palabra)) return true;
        }
        return false;
    }

    private function detectarSucursal(string $mensaje): ?string
    {
        foreach ($this->sucursalesConocidas as $sucursal) {
            if (str_contains($mensaje, $sucursal)) return $sucursal;
        }
        return null;
    }

    private function obtenerNombreSucursal(?int $id): ?string
    {
        if (!$id) return null;
        $branch = Branch::find($id);
        return $branch ? strtolower($branch->name) : null;
    }

    private function extraerProducto(string $mensaje): ?string
    {
        $palabrasARemover = array_merge(
            $this->palabrasClaveStock,
            $this->palabrasClavePrice,
            $this->palabrasClavePromo,
            $this->palabrasClaveInfoSucursal,
            $this->palabrasAEliminar,
            $this->sucursalesConocidas
        );

        $texto = $mensaje;
        foreach ($palabrasARemover as $palabra) {
            $texto = preg_replace('/\b' . $palabra . '\b/', '', $texto);
        }

        $texto = trim(preg_replace('/\s+/', ' ', $texto));
        return !empty($texto) ? $texto : null;
    }

    // ─── Normalización ─────────────────────────────────────────────────────────

    private function normalizarTexto(string $texto): string
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = $this->removerAcentos($texto);
        $texto = preg_replace('/[^\w\s]/', ' ', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    }

    private function removerAcentos(string $texto): string
    {
        $acentos = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
        ];
        return str_replace(array_keys($acentos), array_values($acentos), $texto);
    }

    // ─── Consultas a BD (sin cambios respecto al original) ─────────────────────

    private function consultarStock(string $mensaje, ?string $sucursal): string
    {
        $producto = $this->extraerProducto($mensaje);

        if (!$producto) {
            return "¿Cuál es el nombre del producto que buscas? Ejemplo: \"¿Hay cemento?\"";
        }

        $query   = Producto::whereRaw('LOWER(nombre) LIKE ?', ["%{$producto}%"]);
        $branchId = null;

        if ($sucursal) {
            $branch = Branch::whereRaw('LOWER(name) LIKE ?', ["%{$sucursal}%"])->first();
            if (!$branch) return "❌ No encontré la sucursal \"{$sucursal}\".";
            $query->where('sucursal_id', $branch->id);
            $branchId = $branch->id;
        }

        $productoModelo = $query->first();

        if (!$productoModelo) {
            $similar = $this->sugerirProductoSimilar($producto);
            if ($similar) {
                return "🤔 No encontré exactamente **{$producto}**.\n\n¿Quisiste decir **{$similar['nombre']}**?\n\n_(Escribe \"sí\" o intenta con otro nombre)_";
            }

            $relacionados = $this->obtenerProductosRelacionados($producto, $branchId);
            if (!$relacionados->isEmpty()) {
                $respuesta = "❌ No tenemos **{$producto}**, pero puedes ver:\n\n";
                foreach ($relacionados as $prod) {
                    $respuesta .= "• **{$prod->nombre}** - \${$prod->precio}\n";
                }
                return $respuesta;
            }

            return "❌ No encontré el producto \"{$producto}\".";
        }

        Session::put('chat_producto', $productoModelo->nombre);
        Session::put('chat_sucursal', $productoModelo->sucursal_id);

        $nombreSucursal = $productoModelo->sucursal ? $productoModelo->sucursal->name : 'desconocida';
        $stock = $productoModelo->stock;

        if ($stock > 0) {
            $alerta = $stock <= 20 ? " ⚠️ *Stock bajo, se recomienda comprar pronto*" : "";
            return "✅ Tenemos **{$stock} unidades** de **{$productoModelo->nombre}** en la **{$nombreSucursal}**{$alerta}.";
        }

        $alternativas = $this->obtenerProductosRelacionados($productoModelo->nombre, $branchId, 3);
        $respuesta = "❌ Lo sentimos, **{$productoModelo->nombre}** está agotado en la **{$nombreSucursal}**.";

        if (!$alternativas->isEmpty()) {
            $respuesta .= "\n\n💡 Alternativas disponibles:\n";
            foreach ($alternativas as $alt) {
                $respuesta .= "• **{$alt->nombre}** - {$alt->stock} unidades disponibles\n";
            }
        }

        return $respuesta;
    }

    private function consultarPrecio(string $mensaje, ?string $sucursal): string
    {
        $producto = $this->extraerProducto($mensaje);

        if (!$producto) {
            return "¿Cuál es el nombre del producto? Ejemplo: \"¿Cuál es el precio del cemento?\"";
        }

        $query    = Producto::whereRaw('LOWER(nombre) LIKE ?', ["%{$producto}%"]);
        $branchId = null;

        if ($sucursal) {
            $branch = Branch::whereRaw('LOWER(name) LIKE ?', ["%{$sucursal}%"])->first();
            if (!$branch) return "❌ No encontré la sucursal \"{$sucursal}\".";
            $query->where('sucursal_id', $branch->id);
            $branchId = $branch->id;
        }

        $productoModelo = $query->first();

        if (!$productoModelo) {
            $similar = $this->sugerirProductoSimilar($producto);
            if ($similar) {
                return "🤔 No encontré exactamente **{$producto}**.\n\n¿Quisiste decir **{$similar['nombre']}**?\n\n_(Escribe \"sí\" o intenta con otro nombre)_";
            }

            $relacionados = $this->obtenerProductosRelacionados($producto, $branchId);
            if (!$relacionados->isEmpty()) {
                $respuesta = "❌ No tenemos **{$producto}**, pero puedes ver:\n\n";
                foreach ($relacionados as $prod) {
                    $respuesta .= "• **{$prod->nombre}** - \${$prod->precio}\n";
                }
                return $respuesta;
            }

            return "❌ No encontré el producto \"{$producto}\".";
        }

        Session::put('chat_producto', $productoModelo->nombre);
        Session::put('chat_sucursal', $productoModelo->sucursal_id);

        $nombreSucursal = $productoModelo->sucursal ? $productoModelo->sucursal->name : 'desconocida';
        return "💰 El precio de **{$productoModelo->nombre}** es **\${$productoModelo->precio}** en la sucursal **{$nombreSucursal}**.";
    }

    private function consultarPromociones(?string $sucursal): string
    {
        $query = Discount::active();

        if ($sucursal) {
            $branch = Branch::whereRaw('LOWER(name) LIKE ?', ["%{$sucursal}%"])->first();
            if (!$branch) return "❌ No encontré la sucursal \"{$sucursal}\".";
            $query->whereHas('branches', fn($q) => $q->where('sucursal_id', $branch->id));
        }

        $promociones = $query->get();

        if ($promociones->isEmpty()) {
            return "📌 No hay promociones activas en este momento.";
        }

        $respuesta = "🎉 **Promociones Activas:**\n\n";
        foreach ($promociones as $promo) {
            $valor = $promo->type === 'percentage' ? "{$promo->value}%" : "\${$promo->value}";
            $respuesta .= "• **{$promo->name}**: {$valor}\n";
            if ($promo->fecha_fin) {
                $respuesta .= "  *Válido hasta: " . $promo->fecha_fin->format('d/m/Y') . "*\n";
            }
        }

        return $respuesta;
    }

    private function consultarHorario(): string
    {
        return "🕐 **Horario:**\n\nLunes a Viernes: **8:00 AM - 5:00 PM**\nSábado y Domingo: Cerrado";
    }

    private function consultarUbicacion(?string $sucursal): string
    {
        if (!$sucursal) {
            return "¿De cuál sucursal quieres conocer la ubicación? (norte, sur, centro, este, oeste)";
        }

        $branch = Branch::whereRaw('LOWER(name) LIKE ?', ["%{$sucursal}%"])->first();
        if (!$branch) return "❌ No encontré la sucursal \"{$sucursal}\".";

        return "📍 **{$branch->name}**\n\nDirección: {$branch->address}";
    }

    private function consultarContacto(?string $sucursal): string
    {
        if (!$sucursal) {
            return "¿De cuál sucursal quieres el contacto? (norte, sur, centro, este, oeste)";
        }

        $branch = Branch::whereRaw('LOWER(name) LIKE ?', ["%{$sucursal}%"])->first();
        if (!$branch) return "❌ No encontré la sucursal \"{$sucursal}\".";

        return "📞 **Contacto - {$branch->name}**\n\n☎️ Teléfono: {$branch->phone}\n📍 Dirección: {$branch->address}";
    }

    private function consultarInfoSucursal(?string $sucursal): string
    {
        if (!$sucursal) {
            return "¿De cuál sucursal quieres información? (norte, sur, centro, este, oeste)";
        }

        $branch = Branch::whereRaw('LOWER(name) LIKE ?', ["%{$sucursal}%"])->first();
        if (!$branch) return "❌ No encontré la sucursal \"{$sucursal}\".";

        return "🏢 **{$branch->name}**\n\n"
            . "📍 **Dirección:** {$branch->address}\n"
            . "📞 **Teléfono:** {$branch->phone}\n"
            . "🕐 **Horario:** Lunes a Viernes 8:00 AM - 5:00 PM\n"
            . "📅 **Atendemos:** Lunes a Sábado";
    }

    private function respuestaNoEntendida(): string
    {
        return "❓ No entendí tu pregunta. Puedo ayudarte con:\n\n"
            . "📦 **Stock:** \"¿Hay cemento?\" o \"¿Hay martillos en sucursal centro?\"\n"
            . "💰 **Precios:** \"Precio del cemento\" o \"¿Cuánto cuesta en sucursal norte?\"\n"
            . "🎉 **Promociones:** \"¿Hay descuentos?\" o \"Ofertas en sucursal centro\"\n"
            . "🏢 **Sucursal:** \"Información de sucursal centro\"\n"
            . "🕐 **Horario:** \"¿Cuál es tu horario?\"\n"
            . "📞 **Contacto:** \"Teléfono de sucursal norte\"";
    }

    // ─── Helpers fuzzy search (sin cambios) ────────────────────────────────────

    private function sugerirProductoSimilar(string $textoIngresado): ?array
    {
        $productosUnicos = Producto::select('nombre')->distinct()->get()->pluck('nombre')->toArray();

        if (empty($productosUnicos)) return null;

        $mejorCoincidencia = null;
        $mejorPuntaje      = 0;
        $umbralMinimo      = 60;

        foreach ($productosUnicos as $nombreProducto) {
            $nombreNorm = $this->normalizarTexto($nombreProducto);
            $similitud  = 0;
            similar_text($textoIngresado, $nombreNorm, $similitud);

            if ($similitud < $umbralMinimo) {
                $distancia = levenshtein($textoIngresado, $nombreNorm);
                $maxLen    = max(strlen($textoIngresado), strlen($nombreNorm));
                if ($maxLen > 0) {
                    $similitud = (1 - ($distancia / $maxLen)) * 100;
                }
            }

            if ($similitud > $mejorPuntaje && $similitud >= $umbralMinimo) {
                $mejorPuntaje      = $similitud;
                $mejorCoincidencia = ['nombre' => $nombreProducto, 'similitud' => (int)$similitud];
            }
        }

        return $mejorCoincidencia;
    }

    private function obtenerProductosRelacionados(string $nombreProducto, ?int $branchId = null, int $limite = 3)
    {
        $nombreNorm = $this->normalizarTexto($nombreProducto);

        $query = Producto::query()
            ->where('stock', '>', 0)
            ->limit($limite);

        if ($branchId) $query->where('sucursal_id', $branchId);

        $query->whereRaw('LOWER(nombre) LIKE ?', ["%{$nombreNorm}%"]);
        $productos = $query->get();

        if ($productos->isEmpty()) {
            $todos = Producto::where('stock', '>', 0)
                ->when($branchId, fn($q) => $q->where('sucursal_id', $branchId))
                ->get();

            return $todos->map(function ($prod) use ($nombreNorm) {
                $similitud = 0;
                similar_text($nombreNorm, $this->normalizarTexto($prod->nombre), $similitud);
                return ['producto' => $prod, 'similitud' => $similitud];
            })
            ->filter(fn($item) => $item['similitud'] > 40)
            ->sortByDesc('similitud')
            ->take($limite)
            ->pluck('producto');
        }

        return $productos;
    }
}