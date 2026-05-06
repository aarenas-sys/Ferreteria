<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FerreNet Chat</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-login.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .chat-header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .chat-header p  { margin: 5px 0 0; opacity: .9; font-size: 14px; }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message { display: flex; animation: slideIn .3s ease-out; }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .message.user { justify-content: flex-end; }
        .message.bot  { justify-content: flex-start; }

        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            line-height: 1.5;
        }

        .message.user .message-bubble {
            background-color: #667eea;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.bot .message-bubble {
            background-color: #f0f0f0;
            color: #333;
            border-bottom-left-radius: 4px;
        }

        /* ── Miniatura de imagen en burbuja de usuario ── */
        .message-image-preview {
            max-width: 180px;
            max-height: 180px;
            border-radius: 12px;
            object-fit: cover;
            display: block;
            margin-bottom: 6px;
            border: 2px solid rgba(255,255,255,0.3);
        }

        /* ── Área de input ── */
        .chat-input-area {
            padding: 12px 20px 16px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        /* Miniatura de preview antes de enviar */
        .preview-strip {
            display: none;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            padding: 8px 12px;
            background: #f4f4f4;
            border-radius: 12px;
        }

        .preview-strip.show { display: flex; }

        .preview-strip img {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #667eea;
        }

        .preview-strip .preview-info { flex: 1; font-size: 12px; color: #555; }
        .preview-strip .preview-info span { display: block; font-weight: 600; color: #333; }

        .preview-strip .remove-img {
            cursor: pointer;
            color: #d32f2f;
            font-size: 18px;
            font-weight: bold;
            line-height: 1;
            padding: 2px 6px;
            border-radius: 50%;
            transition: background .2s;
        }
        .preview-strip .remove-img:hover { background: #fde8e8; }

        .input-row {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .chat-input-area input[type="file"] { display: none; }

        .image-upload-btn {
            padding: 10px 14px;
            background: #f0f0f0;
            color: #333;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            cursor: pointer;
            font-size: 18px;
            transition: all .3s;
            white-space: nowrap;
        }

        .image-upload-btn:hover           { background: #e0e0e0; border-color: #667eea; }
        .image-upload-btn.selected        { background: #d4e7ff; border-color: #667eea; }
        .image-upload-btn:disabled        { opacity: .5; cursor: not-allowed; }

        .chat-input-area input[type="text"] {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color .3s;
        }

        .chat-input-area input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }

        .chat-input-area button#sendBtn {
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background .3s;
            white-space: nowrap;
        }

        .chat-input-area button#sendBtn:hover     { background: #5568d3; }
        .chat-input-area button#sendBtn:disabled  { background: #ccc; cursor: not-allowed; }

        /* ── Typing indicator ── */
        .typing-indicator {
            display: flex;
            gap: 4px;
            align-items: center;
            padding: 12px 16px;
            background-color: #f0f0f0;
            border-radius: 18px;
            width: fit-content;
        }

        .typing-indicator span {
            width: 8px; height: 8px;
            border-radius: 50%;
            background-color: #999;
            animation: typing 1.4s infinite;
        }

        .typing-indicator span:nth-child(2) { animation-delay: .2s; }
        .typing-indicator span:nth-child(3) { animation-delay: .4s; }

        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); opacity: .7; }
            30%            { transform: translateY(-10px); opacity: 1; }
        }

        /* ── Empty state ── */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: white;
            text-align: center;
        }

        .empty-state-icon { font-size: 64px; margin-bottom: 20px; }
        .empty-state h2   { font-size: 24px; margin-bottom: 10px; }
        .empty-state p    { font-size: 16px; opacity: .9; max-width: 500px; }

        .quick-suggestions {
            display: flex; flex-wrap: wrap;
            gap: 8px; margin-top: 20px;
            justify-content: center;
        }

        .suggestion-btn {
            padding: 8px 16px;
            background: rgba(255,255,255,.2);
            color: white;
            border: 1px solid rgba(255,255,255,.5);
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            transition: all .3s;
        }
        .suggestion-btn:hover { background: rgba(255,255,255,.3); border-color: rgba(255,255,255,.8); }

        /* ── Markdown ── */
        .message-bubble strong { font-weight: 600; }
        .message-bubble em     { font-style: italic; }
        .message-bubble ul     { margin: 10px 0; padding-left: 20px; }
        .message-bubble li     { margin: 5px 0; }
    </style>
</head>
<body class="m-0 p-0">
<div class="chat-container">

    <!-- Header -->
    <div class="chat-header">
        <h1>🏗️ FerreNet Chat</h1>
        <p>Asistente automático - Consulta disponibilidad, precios y promociones</p>
    </div>

    <!-- Mensajes -->
    <div class="chat-messages" id="chatMessages">
        <div class="empty-state">
            <div class="empty-state-icon">💬</div>
            <h2>¡Hola! Bienvenido a FerreNet</h2>
            <p>Soy tu asistente. Puedes escribir tu pregunta o subir una foto del producto que buscas.</p>
            <div class="quick-suggestions">
                <button class="suggestion-btn" onclick="sendQuickMessage('¿Hay cemento?')">¿Hay cemento?</button>
                <button class="suggestion-btn" onclick="sendQuickMessage('¿Cuál es el precio del fierro?')">¿Precio del fierro?</button>
                <button class="suggestion-btn" onclick="sendQuickMessage('¿Qué promociones tienen?')">¿Promociones?</button>
                <button class="suggestion-btn" onclick="sendQuickMessage('¿Cuál es tu horario?')">¿Horario?</button>
            </div>
        </div>
    </div>

    <!-- Input area -->
    <div class="chat-input-area">
        <!-- Miniatura preview antes de enviar -->
        <div class="preview-strip" id="previewStrip">
            <img id="previewThumb" src="" alt="preview">
            <div class="preview-info">
                <span id="previewName"></span>
                📷 Imagen lista — escribe tu pregunta y envía
            </div>
            <span class="remove-img" onclick="removeImage()" title="Quitar imagen">✕</span>
        </div>

        <!-- Fila de inputs -->
        <div class="input-row">
            <input type="file" id="imageInput" accept="image/jpeg,image/jpg,image/png">
            <button class="image-upload-btn" id="uploadBtn"
                    onclick="document.getElementById('imageInput').click(); event.stopPropagation();"
                    title="Subir imagen (JPG, PNG - máx 10MB)">
                📷
            </button>
            <input type="text" id="messageInput"
                   placeholder="Escribe tu pregunta o sube una imagen..."
                   autocomplete="off">
            <button id="sendBtn" onclick="sendMessage()">Enviar</button>
        </div>
    </div>

</div>

<script>
    // ── Referencias DOM ───────────────────────────────────────────────────────
    const chatMessages  = document.getElementById('chatMessages');
    const messageInput  = document.getElementById('messageInput');
    const imageInput    = document.getElementById('imageInput');
    const uploadBtn     = document.getElementById('uploadBtn');
    const sendBtn       = document.getElementById('sendBtn');
    const previewStrip  = document.getElementById('previewStrip');
    const previewThumb  = document.getElementById('previewThumb');
    const previewName   = document.getElementById('previewName');

    const OPCIONES_PREFIX = '__OPCIONES__';

    let firstMessage  = true;
    let selectedFile  = null;
    let previewDataUrl = null; // base64 para mostrar en burbuja del usuario

    messageInput.focus();

    // ── Selección de imagen ───────────────────────────────────────────────────
    imageInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) { removeImage(); return; }

        const allowed = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowed.includes(file.type)) {
            alert('❌ Solo se permiten imágenes JPG y PNG');
            imageInput.value = '';
            return;
        }

        if (file.size > 10240 * 1024) {
            alert('❌ La imagen no debe exceder 10MB');
            imageInput.value = '';
            return;
        }

        selectedFile = file;
        uploadBtn.classList.add('selected');

        // Leer como DataURL para mostrar miniatura
        const reader = new FileReader();
        reader.onload = (ev) => {
            previewDataUrl = ev.target.result;
            previewThumb.src  = previewDataUrl;
            previewName.textContent = file.name;
            previewStrip.classList.add('show');
        };
        reader.readAsDataURL(file);

        messageInput.placeholder = '¿Hay este producto? ¿Cuál es su precio?...';
        messageInput.focus();
    });

    function removeImage() {
        selectedFile   = null;
        previewDataUrl = null;
        imageInput.value = '';
        uploadBtn.classList.remove('selected');
        previewStrip.classList.remove('show');
        previewThumb.src = '';
        messageInput.placeholder = 'Escribe tu pregunta o sube una imagen...';
        messageInput.focus();
    }

    // ── Enviar con Enter ──────────────────────────────────────────────────────
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // ── Enviar mensaje ────────────────────────────────────────────────────────
    async function sendMessage() {
        const mensaje = messageInput.value.trim();

        if (!mensaje && !selectedFile) {
            alert('Escribe un mensaje o sube una imagen');
            return;
        }

        // Limpiar empty state al primer mensaje
        if (firstMessage) {
            chatMessages.innerHTML = '';
            firstMessage = false;
        }

        // Mostrar mensaje del usuario en el chat
        addUserMessage(mensaje, previewDataUrl);

        // Deshabilitar controles
        setControls(false);
        showTypingIndicator();

        try {
            let data;

            if (selectedFile) {
                // ── Envío con imagen → FormData → /chat/imagen ────────────────
                const formData = new FormData();
                formData.append('imagen', selectedFile);
                if (mensaje) formData.append('mensaje', mensaje);

                const response = await fetch('/chat/imagen', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData,
                });

                data = await response.json();

            } else {
                // ── Envío solo texto → JSON → /chat ───────────────────────────
                const response = await fetch('/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ mensaje }),
                });

                data = await response.json();
            }

            removeTypingIndicator();
            addMessage(data.respuesta ?? 'Sin respuesta.', 'bot');

        } catch (error) {
            removeTypingIndicator();
            console.error('Error:', error);
            addMessage('❌ Error de conexión. Verifica tu conexión e intenta de nuevo.', 'bot');
        } finally {
            messageInput.value = '';
            removeImage();
            setControls(true);
            messageInput.focus();
        }
    }

    function sendQuickMessage(mensaje) {
        messageInput.value = mensaje;
        sendMessage();
    }

    // ── Habilitar / deshabilitar controles ────────────────────────────────────
    function setControls(enabled) {
        sendBtn.disabled      = !enabled;
        messageInput.disabled = !enabled;
        uploadBtn.disabled    = !enabled;
    }

    // ── Agregar mensaje del usuario (con miniatura si hay imagen) ─────────────
    function addUserMessage(texto, imgDataUrl) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message user';

        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';

        // Miniatura de imagen si existe
        if (imgDataUrl) {
            const img = document.createElement('img');
            img.src = imgDataUrl;
            img.className = 'message-image-preview';
            img.alt = 'imagen enviada';
            bubble.appendChild(img);
        }

        // Texto del mensaje
        if (texto) {
            const p = document.createElement('span');
            p.textContent = texto;
            bubble.appendChild(p);
        } else if (imgDataUrl) {
            const p = document.createElement('span');
            p.textContent = '📷 Identificar producto';
            bubble.appendChild(p);
        }

        messageDiv.appendChild(bubble);
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // ── Agregar mensaje del bot (con soporte de opciones/botones) ─────────────
    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;

        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';

        // Detectar respuesta con botones de opciones (sucursales o productos)
        if (sender === 'bot' && text.includes(OPCIONES_PREFIX)) {
            const partes       = text.split(OPCIONES_PREFIX);
            const textoMensaje = partes[0].trim();
            const opcionesRaw  = partes[1]?.split('\n')[0]?.trim() || '[]';

            let opciones = [];
            try { opciones = JSON.parse(opcionesRaw); } catch (e) { console.error(e); }

            if (textoMensaje) {
                const p = document.createElement('p');
                p.style.marginBottom = '12px';
                p.innerHTML = formatMarkdown(textoMensaje);
                bubble.appendChild(p);
            }

            if (opciones.length) {
                const btnContainer = document.createElement('div');
                btnContainer.style.cssText = 'display:flex;flex-wrap:wrap;gap:8px;margin-top:4px;';

                opciones.forEach(op => {
                    const btn = document.createElement('button');
                    btn.textContent = '🏢 ' + op.label;
                    btn.style.cssText = `
                        padding:8px 16px;background:#667eea;color:white;
                        border:none;border-radius:20px;cursor:pointer;
                        font-size:13px;font-weight:600;transition:background .2s;
                    `;
                    btn.onmouseover = () => btn.style.background = '#5568d3';
                    btn.onmouseout  = () => btn.style.background = '#667eea';
                    btn.onclick = () => {
                        btnContainer.querySelectorAll('button').forEach(b => {
                            b.disabled = true;
                            b.style.opacity = '0.5';
                            b.style.cursor  = 'not-allowed';
                        });
                        sendQuickMessage(op.value);
                    };
                    btnContainer.appendChild(btn);
                });

                bubble.appendChild(btnContainer);
            }

            // Texto adicional después de las opciones (ej: "_¿No es ninguno?_")
            const restoTexto = partes[1]?.substring(opcionesRaw.length).trim();
            if (restoTexto) {
                const p2 = document.createElement('p');
                p2.style.cssText = 'margin-top:10px;font-size:12px;color:#666;';
                p2.innerHTML = formatMarkdown(restoTexto);
                bubble.appendChild(p2);
            }

            messageDiv.appendChild(bubble);
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            return;
        }

        // Mensaje de texto normal
        bubble.innerHTML = formatMarkdown(text);
        messageDiv.appendChild(bubble);
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // ── Markdown simple → HTML ────────────────────────────────────────────────
    function formatMarkdown(text) {
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/__(.*?)__/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>')
            .replace(/• /g, '<li>');
    }

    // ── Typing indicator ──────────────────────────────────────────────────────
    function showTypingIndicator() {
        const div = document.createElement('div');
        div.className = 'message bot';
        div.id = 'typingIndicator';

        const ind = document.createElement('div');
        ind.className = 'typing-indicator';
        ind.innerHTML = '<span></span><span></span><span></span>';

        div.appendChild(ind);
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function removeTypingIndicator() {
        document.getElementById('typingIndicator')?.remove();
    }
</script>
</body>
</html>