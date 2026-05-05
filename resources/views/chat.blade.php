<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FerreNet Chat</title>
    
    <!-- Favicon -->
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .chat-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .chat-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message {
            display: flex;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.user {
            justify-content: flex-end;
        }

        .message.bot {
            justify-content: flex-start;
        }

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

        .chat-input-area {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .chat-input-wrapper {
            flex: 1;
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .chat-input-area input[type="text"] {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .chat-input-area input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }

        .chat-input-area input[type="file"] {
            display: none;
        }

        .image-upload-btn {
            padding: 10px 15px;
            background: #f0f0f0;
            color: #333;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .image-upload-btn:hover {
            background: #e0e0e0;
            border-color: #667eea;
        }

        .image-upload-btn.selected {
            background: #d4e7ff;
            border-color: #667eea;
            color: #667eea;
            font-weight: 600;
        }

        .image-preview {
            display: none;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            padding: 5px 10px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .image-preview.show {
            display: block;
        }

        .image-preview .remove-btn {
            margin-left: 8px;
            cursor: pointer;
            color: #d32f2f;
            font-weight: bold;
        }

        .chat-input-area button {
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
            white-space: nowrap;
        }

        .chat-input-area button:hover {
            background: #5568d3;
        }

        .chat-input-area button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

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
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #999;
            animation: typing 1.4s infinite;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
                opacity: 0.7;
            }
            30% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: white;
            text-align: center;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 16px;
            opacity: 0.9;
            max-width: 500px;
        }

        .quick-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 20px;
            justify-content: center;
        }

        .suggestion-btn {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }

        .suggestion-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.8);
        }

        /* Markdown support */
        .message-bubble strong {
            font-weight: 600;
        }

        .message-bubble em {
            font-style: italic;
        }

        .message-bubble ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .message-bubble li {
            margin: 5px 0;
        }
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
                <p>Soy tu asistente. Puedo ayudarte con consultas sobre productos, precios, promociones, horarios y contacto.</p>
                <div class="quick-suggestions">
                    <button class="suggestion-btn" onclick="sendQuickMessage('¿Hay cemento?')">¿Hay cemento?</button>
                    <button class="suggestion-btn" onclick="sendQuickMessage('¿Cuál es el precio del fierro?')">¿Precio del fierro?</button>
                    <button class="suggestion-btn" onclick="sendQuickMessage('¿Qué promociones tienen?')">¿Promociones?</button>
                    <button class="suggestion-btn" onclick="sendQuickMessage('¿Cuál es tu horario?')">¿Horario?</button>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="chat-input-area">
            <div class="chat-input-wrapper">
                <input 
                    type="file" 
                    id="imageInput" 
                    accept="image/jpeg,image/jpg,image/png"
                >
                <button class="image-upload-btn" id="uploadBtn" onclick="document.getElementById('imageInput').click(); event.stopPropagation();" title="Subir imagen (JPG, PNG - máximo 10MB)">
                    📷
                </button>
                <input 
                    type="text" 
                    id="messageInput" 
                    placeholder="Escribe tu pregunta..." 
                    autocomplete="off"
                >
            </div>
            <button id="sendBtn" onclick="sendMessage()">Enviar</button>
        </div>
        <div class="image-preview" id="imagePreview"></div>
    </div>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const imageInput = document.getElementById('imageInput');
        const uploadBtn = document.getElementById('uploadBtn');
        const imagePreview = document.getElementById('imagePreview');
        const sendBtn = document.getElementById('sendBtn');
        let firstMessage = true;
        let selectedFile = null;

        // Enfocar input al cargar
        messageInput.focus();

        // Manejar selección de archivo
        imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            
            if (!file) {
                selectedFile = null;
                uploadBtn.classList.remove('selected');
                imagePreview.classList.remove('show');
                return;
            }

            // Validar tipo de archivo
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                alert('❌ Solo se permiten imágenes JPG y PNG');
                imageInput.value = '';
                return;
            }

            // Validar tamaño (máximo 10MB = 10240 KB)
            const maxSizeKB = 10240;
            if (file.size > maxSizeKB * 1024) {
                alert('❌ La imagen no debe exceder 10MB');
                imageInput.value = '';
                return;
            }

            // Guardar archivo y mostrar preview
            selectedFile = file;
            uploadBtn.classList.add('selected');
            imagePreview.classList.add('show');
            imagePreview.innerHTML = `✔ ${file.name} <span class="remove-btn" onclick="removeImage()">✕</span>`;
        });

        function removeImage() {
            selectedFile = null;
            imageInput.value = '';
            uploadBtn.classList.remove('selected');
            imagePreview.classList.remove('show');
            messageInput.focus();
        }

        // Enviar con Enter
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        async function sendMessage() {
            const mensaje = messageInput.value.trim();
            if (!mensaje && !selectedFile) {
                alert('Escribe un mensaje o sube una imagen');
                return;
            }

            // Mostrar mensaje del usuario inmediatamente
            if (firstMessage) {
                chatMessages.innerHTML = '';
                firstMessage = false;
            }

            // Agregar mensaje del usuario a la UI
            if (mensaje) {
                let userMessageText = mensaje;
                if (selectedFile) {
                    userMessageText = `📷 ${userMessageText}`;
                }
                addMessage(userMessageText, 'user');
            } else if (selectedFile) {
                addMessage('📷 Imagen cargada', 'user');
            }

            // Mostrar indicador de escritura
            showTypingIndicator();

            // Desactivar controles durante envío
            sendBtn.disabled = true;
            messageInput.disabled = true;
            uploadBtn.disabled = true;

            try {
                // Si hay mensaje, enviarlo al endpoint /chat
                if (mensaje) {
                    const response = await fetch('/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({ mensaje })
                    });

                    const data = await response.json();
                    removeTypingIndicator();

                    if (response.ok) {
                        addMessage(data.respuesta, 'bot');
                    } else {
                        addMessage('Hubo un error procesando tu mensaje. Por favor intenta de nuevo.', 'bot');
                    }
                } else if (selectedFile) {
                    // Si solo hay imagen, mostrar confirmación
                    removeTypingIndicator();
                    addMessage('✔ Imagen cargada - escribe el nombre del producto', 'bot');
                }

            } catch (error) {
                removeTypingIndicator();
                console.error('Error:', error);
                addMessage('Error de conexión. Por favor verifica tu conexión e intenta de nuevo.', 'bot');
            } finally {
                // Limpiar y reactivar
                messageInput.value = '';
                removeImage();
                
                sendBtn.disabled = false;
                messageInput.disabled = false;
                uploadBtn.disabled = false;
                messageInput.focus();
            }
        }

        function sendQuickMessage(mensaje) {
            messageInput.value = mensaje;
            sendMessage();
        }

        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;

            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            
            // Convertir markdown simple a HTML
            text = text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') // **bold**
                .replace(/__(.*?)__/g, '<strong>$1</strong>') // __bold__
                .replace(/\n/g, '<br>') // saltos de línea
                .replace(/• /g, '<li>') // viñetas
                .replace(/^/gm, '<ul>'); // listas

            bubble.innerHTML = text;
            messageDiv.appendChild(bubble);
            chatMessages.appendChild(messageDiv);

            // Scroll al final
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTypingIndicator() {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message bot';
            messageDiv.id = 'typingIndicator';

            const indicator = document.createElement('div');
            indicator.className = 'typing-indicator';
            indicator.innerHTML = '<span></span><span></span><span></span>';

            messageDiv.appendChild(indicator);
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function removeTypingIndicator() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) {
                indicator.remove();
            }
        }
    </script>
</body>
</html>
