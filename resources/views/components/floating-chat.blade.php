<div class="fixed bottom-6 right-6 z-50" x-data="floatingChat()" x-init="init()">
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
        x-transition
        class="absolute bottom-20 right-0 w-96 h-[480px] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden"
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
                    class="text-white hover:bg-white hover:bg-opacity-20 w-8 h-8 rounded-full transition">
                    ✕
                </button>
            </div>
        </div>

        <!-- Mensajes -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50 dark:bg-gray-900">
            <!-- Mensaje inicial -->
            <div x-show="messages.length === 0" class="text-center py-8">
                <div class="text-4xl mb-2">👋</div>
                <p class="text-gray-600 dark:text-gray-400 text-sm">¡Hola! Soy tu asistente.</p>
                <p class="text-gray-500 dark:text-gray-500 text-xs mt-2">¿Cómo puedo ayudarte?</p>
            </div>

            <!-- Mensajes del chat -->
            <template x-for="(msg, idx) in messages" :key="idx">
                <div :class="msg.tipo === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="`max-w-xs px-4 py-2 rounded-lg ${
                        msg.tipo === 'user' 
                            ? 'bg-blue-500 text-white rounded-br-none' 
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-bl-none'
                    }`">
                        <p class="text-sm" x-html="msg.texto"></p>
                    </div>
                </div>
            </template>

            <!-- Indicador de escritura -->
            <div x-show="escribiendo" class="flex justify-start">
                <div class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100 px-4 py-2 rounded-lg rounded-bl-none">
                    <div class="flex space-x-1">
                        <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"></span>
                        <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
                        <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-3 bg-white dark:bg-gray-800">
            <!-- Input de mensaje y botones -->
            <form @submit.prevent="enviarMensaje" class="flex gap-2">
                <input 
                    type="text"
                    x-model="nuevoMensaje"
                    placeholder="Escribe tu pregunta..."
                    :disabled="escribiendo"
                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-full focus:outline-none focus:border-blue-500 dark:focus:border-blue-400 text-sm disabled:bg-gray-100 dark:disabled:bg-gray-600"
                    @keyup.enter="enviarMensaje">
                
                <!-- Botón 📷 (al lado del ➤) -->
                <label class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 cursor-pointer transition text-lg" 
                    :class="imagenCargada && 'bg-green-500 hover:bg-green-600 dark:bg-green-600'"
                    :title="imagenCargada ? 'Imagen cargada (✓)' : 'Subir imagen (JPG, PNG - máximo 10MB)'">
                    <span x-text="imagenCargada ? '✓' : '📷'"></span>
                    <input 
                        type="file"
                        @change="cargarImagen"
                        accept="image/jpeg,image/jpg,image/png"
                        :disabled="escribiendo"
                        class="hidden"
                        x-ref="imagenInput">
                </label>

                <!-- Botón ➤ (enviar) -->
                <button 
                    type="submit"
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

        init() {
            // Cargar mensajes de localStorage al inicializar
            const mensajesGuardados = localStorage.getItem('chatMessages');
            if (mensajesGuardados) {
                try {
                    this.messages = JSON.parse(mensajesGuardados);
                    console.log('💾 Mensajes del chat restaurados desde localStorage');
                } catch (e) {
                    console.error('Error al cargar mensajes:', e);
                    this.messages = [];
                }
            }
        },

        guardarMensajes() {
            // Guardar mensajes en localStorage cada vez que se agregue uno
            localStorage.setItem('chatMessages', JSON.stringify(this.messages));
            console.log('💾 Mensajes guardados en localStorage');
        },

        resetChat() {
            // Reiniciar chat SOLO cuando el usuario presiona el botón
            this.messages = [];
            this.nuevoMensaje = '';
            this.escribiendo = false;
            this.imagenCargada = false;
            this.archivoImagen = null;
            this.$refs.imagenInput.value = '';
            localStorage.removeItem('chatMessages');
            console.log('🔄 Chat reiniciado - localStorage limpiado');
        },

        async enviarMensaje() {
            const mensaje = this.nuevoMensaje.trim();
            
            // Si no hay imagen ni mensaje, no enviar
            if (!mensaje && !this.imagenCargada) return;

            // Mostrar mensaje del usuario
            let textoMensaje = mensaje;
            if (this.imagenCargada) {
                textoMensaje = '📷 ' + mensaje;
            }
            
            this.messages.push({
                tipo: 'user',
                texto: textoMensaje
            });
            this.guardarMensajes();

            this.nuevoMensaje = '';
            this.escribiendo = true;

            try {
                // Envío simple por texto al endpoint /chat
                const response = await fetch('/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ mensaje })
                });

                const data = await response.json();

                if (response.ok) {
                    const respuestaSanitizada = this.sanitizarRespuesta(data.respuesta);
                    this.messages.push({
                        tipo: 'bot',
                        texto: respuestaSanitizada
                    });
                    this.guardarMensajes();

                    // Limpiar imagen después de enviar
                    this.imagenCargada = false;
                    this.archivoImagen = null;
                    this.$refs.imagenInput.value = '';
                } else {
                    this.messages.push({
                        tipo: 'bot',
                        texto: '❌ Hubo un error. Por favor intenta de nuevo.'
                    });
                    this.guardarMensajes();
                }
            } catch (error) {
                console.error('Error:', error);
                this.messages.push({
                    tipo: 'bot',
                    texto: '❌ Error de conexión. Verifica tu conexión e intenta de nuevo.'
                });
                this.guardarMensajes();
            } finally {
                this.escribiendo = false;
                // Scroll al final
                this.$nextTick(() => {
                    const container = this.$el.querySelector('.overflow-y-auto');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            }
        },

        async cargarImagen(event) {
            const archivo = event.target.files[0];
            if (!archivo) return;

            // Validar tamaño (10MB)
            const maxSize = 10 * 1024 * 1024; // 10MB en bytes
            if (archivo.size > maxSize) {
                const sizeMB = (archivo.size / 1024 / 1024).toFixed(1);
                this.messages.push({
                    tipo: 'bot',
                    texto: `❌ Imagen muy grande: ${sizeMB}MB (máximo 10MB)`
                });
                this.guardarMensajes();
                this.$refs.imagenInput.value = '';
                return;
            }

            // Validar tipos soportados (solo JPG y PNG como especificó el usuario)
            const formatosSoportados = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!formatosSoportados.includes(archivo.type)) {
                this.messages.push({
                    tipo: 'bot',
                    texto: `❌ Formato no soportado: ${archivo.type}. Usa JPG o PNG`
                });
                this.guardarMensajes();
                this.$refs.imagenInput.value = '';
                return;
            }

            // Guardar imagen (SIN ENVIAR)
            this.archivoImagen = archivo;
            this.imagenCargada = true;
            
            // Mostrar en chat que se cargó
            this.messages.push({
                tipo: 'bot',
                texto: `✔ Imagen cargada - escribe el nombre del producto`
            });
            this.guardarMensajes();

            // Scroll al final
            this.$nextTick(() => {
                const container = this.$el.querySelector('.overflow-y-auto');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },

        sanitizarRespuesta(texto) {
            return texto
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/__(.*?)__/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>')
                .replace(/• /g, '<li>')
                .replace(/^(?=<li>)/gm, '<ul>');
        }
    };
}
</script>
