<div class="fixed bottom-4 right-4 z-[9999]" 
     x-data="initSessionTimeout()">
    
    <!-- Alerta de sesión por expirar -->
    <div x-show="showWarning" 
         x-cloak
         class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg shadow-lg max-w-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-yellow-800">
                    ⏱️ Tu sesión está por expirar
                </h3>
                <p class="mt-2 text-sm text-yellow-700">
                    Tu sesión expirará por inactividad en <span class="font-bold" x-text="formatTime(timeRemaining)"></span>
                </p>
                <div class="mt-4">
                    <div class="w-full bg-yellow-200 rounded-full h-2">
                        <div class="bg-yellow-500 h-2 rounded-full transition-all duration-300" 
                             :style="`width: ${(timeRemaining / totalTimeout) * 100}%`"></div>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button @click.stop="continueSession()" 
                            class="inline-flex items-center px-3 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Continuar sesión
                    </button>
                    <button @click.stop="logout()" 
                            class="inline-flex items-center px-3 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-lg transition-colors">
                        Cerrar sesión
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function initSessionTimeout() {
    return {
        showWarning: false,
        inactivityTimer: null,
        pollingTimer: null,
        timeRemaining: 0,
        totalTimeout: 120,
        warningThreshold: 30,
        inactivitySeconds: 0,
        
        init() {
            console.log('🟢 Session timeout monitoring initialized');
            this.startInactivityTracking();
            this.startPolling();
            
            // Detectar actividad en el documento (solo si NO hay alerta)
            document.addEventListener('mousemove', () => {
                if (!this.showWarning) {
                    console.log('🔄 Activity detected - resetting timer');
                    this.resetInactivityTimer();
                }
            });
            document.addEventListener('keydown', () => {
                if (!this.showWarning) {
                    this.resetInactivityTimer();
                }
            });
            document.addEventListener('click', () => {
                if (!this.showWarning) {
                    this.resetInactivityTimer();
                }
            });
        },

        startInactivityTracking() {
            this.inactivityTimer = setInterval(() => {
                this.inactivitySeconds += 1;
                
                if (this.inactivitySeconds % 10 === 0) {
                    console.log(`⏱️ Inactivity: ${this.inactivitySeconds}s`);
                }
                
                if (this.inactivitySeconds >= (this.totalTimeout - this.warningThreshold)) {
                    if (!this.showWarning) {
                        console.log('⚠️ Session warning - showing alert');
                        this.showWarning = true;
                    }
                    this.timeRemaining = Math.max(0, this.totalTimeout - this.inactivitySeconds);
                }
                
                if (this.inactivitySeconds >= this.totalTimeout) {
                    console.log('❌ Total inactivity timeout reached - auto logout');
                    this.autoLogout();
                }
            }, 1000);
        },

        resetInactivityTimer() {
            this.inactivitySeconds = 0;
            this.showWarning = false;
            this.timeRemaining = 0;
            console.log('🔄 Inactivity timer reset');
        },

        startPolling() {
            // Polling más frecuente: cada 10 segundos en lugar de 30
            // Esto asegura que se verifique la sesión incluso sin interacción en segundo plano
            this.pollingTimer = setInterval(() => {
                console.log('🔄 Polling session status (every 10s)...');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                fetch('/session/ping', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (response.status === 401 || response.status === 419) {
                        console.log('🔴 Server returned 401/419 - session expired on server');
                        this.autoLogout();
                    } else if (!response.ok) {
                        console.warn('⚠️ Unexpected response:', response.status);
                    } else {
                        console.log('✅ Session still valid');
                    }
                })
                .catch(error => {
                    console.error('❌ Polling error (network or server down):', error);
                    // No logout en caso de error de red - podría ser temporal
                });
            }, 10000); // Cada 10 segundos en lugar de 30
        },

        continueSession() {
            console.log('✅ User clicked "Continue session"');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            fetch('/session/ping', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (response.ok) {
                    console.log('✅ Session extended');
                    this.resetInactivityTimer();
                } else {
                    this.autoLogout();
                }
            })
            .catch(error => {
                console.error('Error extending session:', error);
                this.autoLogout();
            });
        },

        autoLogout() {
            console.log('🔴 Auto logout');
            if (this.inactivityTimer) clearInterval(this.inactivityTimer);
            if (this.pollingTimer) clearInterval(this.pollingTimer);
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';
            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
            form.appendChild(token);
            document.body.appendChild(form);
            form.submit();
        },

        logout() {
            console.log('👤 User clicked logout');
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';
            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
            form.appendChild(token);
            document.body.appendChild(form);
            form.submit();
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
        }
    };
}
</script>
