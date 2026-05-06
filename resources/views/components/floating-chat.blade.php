<div class="fixed bottom-6 right-6 z-50" 
     x-data="floatingChat()" 
     x-init="init()"
     x-cloak
     style="display: block;">
    <!-- Botón flotante -->
    <button 
        @click="open = !open"
        class="w-14 h-14 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg hover:shadow-xl transition-all hover:scale-110 flex items-center justify-center dark:from-blue-600 dark:to-blue-700"
        :class="open && 'bg-gradient-to-r from-red-500 to-red-600 dark:from-red-600 dark:to-red-700'"
        title="Abrir chat">
        <span class="text-2xl" x-show="!open">💬</span>
        <span class="text-2xl" x-show="open">✕</span>
    </button>

    <!-- Ventana del chat -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 transform scale-95 translate-y-4"
        class="absolute bottom-20 right-0 w-96 h-[520px] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden"
        @click.outside="open = false">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-700 dark:to-blue-800 text-white p-4 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-lg">Asistente FerreNet</h3>
                <p class="text-sm opacity-90">Siempre disponible</p>
            </div>
            <div class="flex gap-2">
                <button 
                    @click="resetChat()"
                    class="text-white hover:bg-white hover:bg-opacity-20 w-8 h-8 rounded-full transition flex items-center justify-center text-lg"
                    title="Reiniciar chat">
                    🔄
                </button>
                <button 
                    @click="open = false"
                    class="text-white hover:bg-white hover:bg-opacity-20 w-8 h-8 rounded-full transition flex items-center justify-center">
                    ✕
                </button>
            </div>
        </div>

        <!-- Mensajes -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50 dark:bg-gray-900" x-ref="messagesContainer">
            <!-- Estado vacío -->
            <div x-show="messages.length === 0" class="text-center py-8">
                <div class="text-4xl mb-2">👋</div>
                <p class="text-gray-600 dark:text-gray-400 text-sm">¡Hola! Soy tu asistente.</p>
                <p class="text-gray-500 dark:text-gray-500 text-xs mt-2">¿Cómo puedo ayudarte?</p>
            </div>

            <!-- Mensajes -->
            <template x-for="(msg, idx) in messages" :key="idx">
                <div :class="msg.tipo === 'user' ? 'flex justify-end' : 'flex justify-start'">

                    <!-- Burbuja usuario -->
                    <template x-if="msg.tipo === 'user'">
                        <div class="max-w-xs px-4 py-2 rounded-lg bg-blue-500 text-white rounded-br-none">
                            <!-- Miniatura de imagen si existe -->
                            <template x-if="msg.imagen">
                                <img :src="msg.imagen"
                                     class="rounded-lg mb-2 max-w-full"
                                     style="max-height:140px;object-fit:cover;"
                                     alt="imagen enviada">
                            </template>
                            <p class="text-sm" x-text="msg.texto"></p>
                        </div>
                    </template>

                    <!-- Burbuja bot -->
                    <template x-if="msg.tipo === 'bot'">
                        <div class="max-w-xs px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-bl-none">
                            <!-- Texto normal -->
                            <template x-if="!msg.opciones">
                                <p class="text-sm" x-html="msg.texto"></p>
                            </template>
                            <!-- Mensaje con botones de opciones -->
                            <template x-if="msg.opciones">
                                <div>
                                    <p class="text-sm mb-2" x-html="msg.texto"></p>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        <template x-for="(op, oi) in msg.opciones" :key="oi">
                                            <button
                                                @click="seleccionarOpcion(op.value, idx)"
                                                :disabled="msg.opcionElegida !== null"
                                                :class="msg.opcionElegida === oi
                                                    ? 'bg-blue-700 opacity-80 cursor-default'
                                                    : 'bg-blue-500 hover:bg-blue-600'"
                                                class="text-white text-xs px-3 py-1 rounded-full transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                🏢 <span x-text="op.label"></span>
                                            </button>
                                        </template>
                                    </div>
                                    <template x-if="msg.textoExtra">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2" x-html="msg.textoExtra"></p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                </div>
            </template>

            <!-- Indicador de escritura -->
            <div x-show="escribiendo" class="flex justify-start">
                <div class="bg-gray-200 dark:bg-gray-700 px-4 py-2 rounded-lg rounded-bl-none">
                    <div class="flex space-x-1">
                        <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"></span>
                        <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay:0.1s"></span>
                        <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay:0.2s"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview de imagen antes de enviar -->
        <div x-show="imagenCargada && previewUrl"
             class="px-3 pt-2 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-700 rounded-xl p-2">
                <img :src="previewUrl"
                     class="w-12 h-12 rounded-lg object-cover border-2 border-blue-400"
                     alt="preview">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-200 truncate" x-text="nombreArchivo"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">📷 Escribe tu pregunta y envía</p>
                </div>
                <button @click="quitarImagen()"
                        class="text-red-400 hover:text-red-600 font-bold text-lg leading-none px-1"
                        title="Quitar imagen">✕</button>
            </div>
        </div>

        <!-- Input -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-3 bg-white dark:bg-gray-800">
            <form @submit.prevent class="flex gap-2">
                <input 
                    type="text"
                    x-model="nuevoMensaje"
                    :placeholder="imagenCargada ? '¿Hay este producto? ¿Su precio?...' : 'Escribe tu pregunta...'"
                    :disabled="escribiendo"
                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-full focus:outline-none focus:border-blue-500 dark:focus:border-blue-400 text-sm disabled:bg-gray-100 dark:disabled:bg-gray-600"
                   >
                
                <!-- Botón imagen -->
                <label 
                    class="flex items-center justify-center w-10 h-10 rounded-full cursor-pointer transition text-lg"
                    :class="imagenCargada
                        ? 'bg-green-500 hover:bg-green-600 dark:bg-green-600 text-white'
                        : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600'"
                    :title="imagenCargada ? 'Imagen lista ✓' : 'Subir imagen (JPG, PNG - máx 10MB)'">
                    <span x-text="imagenCargada ? '✓' : '📷'"></span>
                    <input 
                        type="file"
                        @change="cargarImagen"
                        accept="image/jpeg,image/jpg,image/png"
                        :disabled="escribiendo"
                        class="hidden"
                        x-ref="imagenInput">
                </label>

                <!-- Botón enviar -->
                <!-- Botón enviar — cambiar type="submit" a type="button" -->
                <button 
                    type="button"
                    @click="enviarMensaje"
                    :disabled="(!nuevoMensaje.trim() && !imagenCargada) || escribiendo"
                    class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 disabled:bg-gray-300 dark:disabled:bg-gray-600 text-white rounded-full w-10 h-10 flex items-center justify-center transition text-lg">
                    ➤
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function floatingChat() {
    return {
        open: false,
        messages: [],
        nuevoMensaje: '',
        escribiendo: false,
        imagenCargada: false,
        archivoImagen: null,
        previewUrl: null,
        nombreArchivo: '',
        enviando: false,



        // ── Inicialización ────────────────────────────────────────────────────
        init() {
            const guardados = localStorage.getItem('chatMessages');
            if (guardados) {
                try {
                    const parsed = JSON.parse(guardados);
                    
                    // ✅ Filtrar mensajes antiguos que contienen texto específico que no debe existir
                    // Esto limpia automáticamente respuestas antiguas del código anterior
                    const TEXTOS_OBSOLETOS = [
                        'Imagen cargada',
                        'imagen cargada correctamente',
                        'escribe el nombre del producto'
                    ];
                    
                    const mensajesFiltrados = parsed.filter(m => {
                        const texto = (m.texto || '').toLowerCase();
                        return !TEXTOS_OBSOLETOS.some(obsoleto => texto.includes(obsoleto.toLowerCase()));
                    });
                    
                    // Si se filtraron mensajes, guardar los limpios
                    if (mensajesFiltrados.length < parsed.length) {
                        console.log(`🧹 Limpiados ${parsed.length - mensajesFiltrados.length} mensajes obsoletos del localStorage`);
                        localStorage.setItem('chatMessages', JSON.stringify(mensajesFiltrados));
                    }
                    
                    // Los mensajes con imagen (previewUrl) no se restauran para no saturar localStorage
                    this.messages = mensajesFiltrados.map(m => ({ ...m, imagen: null }));
                } catch (e) {
                    console.error('Error recuperando mensajes:', e);
                    this.messages = [];
                    localStorage.removeItem('chatMessages');
                }
            }
        },

        // ── Persistencia ──────────────────────────────────────────────────────
        guardarMensajes() {
            // Guardar sin las imágenes en base64 para no saturar localStorage
            const sinImagenes = this.messages.map(m => ({ ...m, imagen: null }));
            localStorage.setItem('chatMessages', JSON.stringify(sinImagenes));
        },

        // ── Reset ─────────────────────────────────────────────────────────────
        async resetChat() {
            this.messages      = [];
            this.nuevoMensaje  = '';
            this.escribiendo   = false;
            this.quitarImagen();
            localStorage.removeItem('chatMessages');
            
            // Limpiar historial de Groq en el servidor
            try {
                const response = await fetch('/chat/clear', {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response.ok) {
                    console.log('✅ Historial de sesión limpiado en el servidor');
                } else {
                    console.warn('⚠️ No se pudo limpiar el historial del servidor:', response.status);
                }
            } catch (error) {
                console.error('Error limpiando historial del servidor:', error);
                // No fallar silenciosamente — el localStorage ya fue limpiado
            }
        },

        // ── Cargar imagen ─────────────────────────────────────────────────────
        cargarImagen(event) {
        const archivo = event.target.files[0];
        if (!archivo) return;

        if (archivo.size > 10 * 1024 * 1024) {
            this.agregarMensajeBot(`❌ Imagen muy grande: ${(archivo.size/1024/1024).toFixed(1)}MB (máx 10MB)`);
            this.$refs.imagenInput.value = '';
            return;
        }

        const permitidos = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!permitidos.includes(archivo.type)) {
            this.agregarMensajeBot(`❌ Formato no soportado. Usa JPG o PNG`);
            this.$refs.imagenInput.value = '';
            return;
        }

        // Solo guardar archivo y mostrar miniatura — sin mensaje al chat
        this.archivoImagen = archivo;
        this.imagenCargada = true;
        this.nombreArchivo = archivo.name;

        const reader = new FileReader();
        reader.onload = (ev) => {
            this.previewUrl = ev.target.result;
        };
        reader.readAsDataURL(archivo);
    },

        // ── Quitar imagen ─────────────────────────────────────────────────────
        quitarImagen() {
            this.imagenCargada = false;
            this.archivoImagen = null;
            this.previewUrl    = null;
            this.nombreArchivo = '';
            if (this.$refs.imagenInput) {
                this.$refs.imagenInput.value = '';
            }
        },

        // ── Enviar mensaje ────────────────────────────────────────────────────
        async enviarMensaje() {

            if (this.enviando) return; // ← bloquea doble envío
            if (this.escribiendo) return;
            
            const mensaje = this.nuevoMensaje.trim();
            if (!mensaje && !this.imagenCargada) return;

            this.enviando = true; // ← activa el bloqueo


             // ← agrega esto temporalmente
            console.log('enviarMensaje llamado desde:', new Error().stack);

            // Mostrar mensaje del usuario con miniatura si hay imagen
            this.messages.push({
                tipo   : 'user',
                texto  : mensaje || '📷 Identificar producto',
                imagen : this.previewUrl || null,   // miniatura en burbuja
            });
            this.guardarMensajes();

            const imagenParaEnviar = this.archivoImagen;
            const previewParaMostrar = this.previewUrl;
            this.nuevoMensaje = '';
            this.quitarImagen();
            this.escribiendo = true;
            this.scrollBottom();

            console.log('enviarMensaje llamado desde:', new Error().stack);
            console.log('imagenParaEnviar:', imagenParaEnviar);
            console.log('tipo:', typeof imagenParaEnviar);

         
            try {
                let data;

                if (imagenParaEnviar) {
                    // ── Con imagen → FormData → /chat/imagen ─────────────────
                   
                    const formData = new FormData();
                    formData.append('imagen', imagenParaEnviar);
                    console.log('→ Enviando a /chat/imagen ✅');
                    if (mensaje) formData.append('mensaje', mensaje);

                    const res = await fetch('/chat/imagen', {
                        method : 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body   : formData,
                    });
                    data = await res.json();
                    console.log('Respuesta backend:', JSON.stringify(data)); // ← esto
                } else {
                    // ── Solo texto → JSON → /chat ─────────────────────────────
                    const res = await fetch('/chat', {
                        method : 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ mensaje }),
                    });
                    data = await res.json();
                     console.log('→ Enviando a /chat ❌ (sin imagen)');
                }

                this.procesarRespuesta(data.respuesta ?? '❌ Sin respuesta.');

            } catch (error) {
                console.error('Chat error:', error);
                this.agregarMensajeBot('❌ Error de conexión. Intenta de nuevo.');
            } finally {
                this.escribiendo = false;
                this.enviando = false; // ← libera el bloqueo
                this.scrollBottom();
            }
        },

        // ── Procesar respuesta del bot (detecta __OPCIONES__) ─────────────────
        procesarRespuesta(texto) {
            const PREFIJO = '__OPCIONES__';

            if (texto.includes(PREFIJO)) {
                const partes      = texto.split(PREFIJO);
                const textoMensaje = this.sanitizar(partes[0].trim());

                // El JSON de opciones termina en el primer salto de línea o al final
                const resto        = partes[1] ?? '[]';
                const finJson      = resto.indexOf('\n');
                const jsonStr      = finJson !== -1 ? resto.substring(0, finJson) : resto;
                const textoExtra   = finJson !== -1 ? this.sanitizar(resto.substring(finJson).trim()) : '';

                let opciones = [];
                try { opciones = JSON.parse(jsonStr.trim()); } catch (e) { console.error(e); }

                this.messages.push({
                    tipo        : 'bot',
                    texto       : textoMensaje,
                    opciones    : opciones,
                    textoExtra  : textoExtra,
                    opcionElegida: null,   // índice del botón elegido (para deshabilitarlos)
                });
            } else {
                this.messages.push({
                    tipo   : 'bot',
                    texto  : this.sanitizar(texto),
                    opciones: null,
                });
            }

            this.guardarMensajes();
            this.scrollBottom();
        },

        // ── Seleccionar opción (sucursal o producto) ──────────────────────────
        seleccionarOpcion(value, msgIdx) {
            // Marcar botón elegido para deshabilitarlos visualmente
            this.messages[msgIdx].opcionElegida = value;

            // Enviar como si el usuario lo hubiera escrito
            this.nuevoMensaje = value;
            this.enviarMensaje();
        },

        // ── Helpers ───────────────────────────────────────────────────────────
        agregarMensajeBot(texto) {
            this.messages.push({ tipo: 'bot', texto, opciones: null });
            this.guardarMensajes();
            this.scrollBottom();
        },

        sanitizar(texto) {
            return texto
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/__(.*?)__/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>')
                .replace(/• /g, '<li>');
        },

        scrollBottom() {
            this.$nextTick(() => {
                const c = this.$refs.messagesContainer;
                if (c) c.scrollTop = c.scrollHeight;
            });
        },
    };
}
</script>