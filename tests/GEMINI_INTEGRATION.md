# Integración Gemini - Chatbot FerreNet

## 📋 Resumen

Se ha implementado una integración **híbrida** del chatbot de FerreNet que combina:

1. **Sistema de reglas basado en palabras clave** (ChatService) - Rápido y confiable
2. **Google Gemini como fallback** - Para interpretación de lenguaje natural

## 🏗️ Arquitectura

### Flujo de procesamiento:

```
Mensaje del usuario
        ↓
ChatService.procesarMensaje()
        ↓
¿Contiene palabras clave conocidas?
        ├─ SÍ → Procesar con intención detectada
        └─ NO → Fallback a GeminiService
                    ↓
                ¿Gemini responde?
                    ├─ SÍ → Parsear JSON e interpretar intent
                    └─ NO → Devolver mensaje genérico de no entendido
```

## 🔧 Componentes

### 1. **ChatService** (`app/Services/ChatService.php`)

**Responsabilidades:**
- Normalizar texto (minúsculas, remover acentos)
- Detectar intención basada en palabras clave
- Ejecutar consultas a base de datos (stock, precio, etc.)
- Fallback a Gemini si no hay coincidencia

**Intenciones soportadas (reglas):**
- `stock` - Consultar disponibilidad de productos
- `precio` - Consultar precio de productos
- `promocion` - Listar promociones activas
- `horario` - Mostrar horario de atención
- `ubicacion` - Mostrar dirección
- `contacto` - Mostrar datos de contacto

**Palabras clave:**
```php
['stock', 'disponible', 'hay', 'tenemos', 'quedan', 'cantidad']
['precio', 'cuesta', 'vale', 'costo', 'cuanto']
['promocion', 'descuento', 'rebaja', 'oferta']
['horario', 'hora', 'abierto', 'cierra']
['ubicacion', 'direccion', 'donde', 'localizacion']
['contacto', 'telefono', 'whatsapp', 'llamar', 'email']
```

### 2. **GeminiService** (`app/Services/GeminiService.php`)

**Responsabilidades:**
- Llamar API de Google Gemini
- Solicitar interpretación de intención en formato JSON
- Parsear respuesta

**Endpoint:**
- URL: `https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent`
- Modelo: `gemini-pro`
- Timeout: 30 segundos

**Prompt Gemini:**
```
Convierte mensaje a JSON con estructura:
{
  "intent": "consultar_stock|consultar_precio|listar_productos|info_sucursal",
  "producto": "nombre_producto",
  "sucursal": "nombre_sucursal"
}
```

### 3. **ChatController** (`app/Http/Controllers/ChatController.php`)

**Endpoint:** `POST /chat`

**Request:**
```json
{
  "mensaje": "¿Hay martillos disponibles?"
}
```

**Response (200):**
```json
{
  "respuesta": "✅ Sí, tenemos **martillos** en stock...",
  "estado": "exito"
}
```

**Response (500):**
```json
{
  "respuesta": "Hubo un error procesando tu mensaje.",
  "estado": "error",
  "detalles": "Error message"
}
```

## 🚀 Instalación y Configuración

### 1. Configurar API Key

En `.env`:
```env
GEMINI_API_KEY=tu_api_key_aqui
```

### 2. Inyección de Dependencias

En `app/Providers/AppServiceProvider.php`:
```php
$this->app->singleton(GeminiService::class);
$this->app->singleton(ChatService::class, function ($app) {
    return new ChatService($app->make(GeminiService::class));
});
```

### 3. Limpiar caché de Laravel

```bash
php artisan cache:clear
composer dump-autoload
```

## 📊 Flujo de datos

### Entrada: "¿Hay martillos?"
1. ChatService normaliza: "hay martillos"
2. Detecta palabra clave "hay" → Intent: "stock"
3. Extrae parámetro: producto = "martillos"
4. Consulta BD: `Producto::where('nombre', 'like', '%martillos%')`
5. Responde: "✅ Sí, tenemos **martillos** en stock..."

### Entrada: "Necesito información sobre herramientas"
1. ChatService normaliza: "necesito informacion sobre herramientas"
2. **No detecta palabras clave**
3. Intent: "desconocida"
4. **Fallback a Gemini**
5. Gemini interpreta y devuelve JSON:
   ```json
   {
     "intent": "listar_productos",
     "producto": "herramientas",
     "sucursal": ""
   }
   ```
6. ChatService procesa el intent de Gemini
7. Responde con lista de productos

## 🔐 Seguridad

- ✅ Validación de entrada con Laravel
- ✅ CSRF protection en requests
- ✅ Timeout en llamadas HTTP (30s)
- ✅ Logging de errores
- ✅ Try-catch para excepciones

## ⚠️ Consideraciones

### Gemini Fallback
- Gemini es llamado **SOLO si las reglas fallan**
- No reemplaza el sistema de reglas, lo complementa
- Mejor rendimiento: reglas son más rápidas
- Mejor confiabilidad: reglas siempre funcionan

### Errores comunes

1. **404 - Model not found:**
   - Verificar que el modelo exista en la API key
   - Verificar versión de API (v1 vs v1beta)

2. **Timeout:**
   - Aumentar timeout si Gemini responde lentamente
   - Implementar caché de respuestas

3. **JSON malformado:**
   - Gemini puede agregar markdown
   - `limpiarRespuestaJSON()` lo maneja

## 📈 Mejoras futuras

1. **Caché de respuestas Gemini**
   - Almacenar respuestas frecuentes
   - Reducir latencia y costos API

2. **Entrenamiento con feedback**
   - Guardar queries con sus respuestas
   - Mejorar precisión de Gemini

3. **Rate limiting**
   - Limitar llamadas a Gemini por usuario
   - Prevenir abuso

4. **Multi-idioma**
   - Extender soporta otros idiomas
   - Traducir respuestas dinámicamente

5. **Context awareness**
   - Mantener histórico de conversación
   - Mejorar relevancia de respuestas

## 🧪 Testing

Ejecutar pruebas del servicio:
```bash
php artisan tinker

# En tinker:
$service = app(App\Services\ChatService::class);
$service->procesarMensaje('¿Hay cemento?');
```

## 📝 Notas técnicas

- **Clase:** `App\Services\ChatService`
- **Método principal:** `procesarMensaje(string $mensaje): string`
- **Dependencias:** GeminiService, Producto, Branch, Discount, Setting
- **Base de datos:** Usa modelos Eloquent
- **Logging:** `\Log::warning()` para errores

## ✅ Estado

- ✅ Integración completada
- ✅ Arquitectura hybrid implementada
- ✅ Fallback a Gemini funcional
- ⏳ Await API key válida para testing completo
- ⏳ Implementar caché de respuestas
