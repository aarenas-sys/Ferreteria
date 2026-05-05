// Session Timeout Manager
document.addEventListener('DOMContentLoaded', () => {
    const sessionAlertEl = document.querySelector('[data-session-timeout]');
    if (!sessionAlertEl) return;

    Alpine.data('sessionTimeout', () => ({
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
            document.addEventListener('mousemove', () => this.handleActivity('mousemove'));
            document.addEventListener('keydown', () => this.handleActivity('keydown'));
            document.addEventListener('click', () => this.handleActivity('click'));
            document.addEventListener('touchstart', () => this.handleActivity('touchstart'));
        },

        handleActivity(eventType) {
            if (!this.showWarning) {
                console.log(`🔄 Activity detected (${eventType}) - resetting timer`);
                this.resetInactivityTimer();
            }
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
            console.log('🔄 Inactivity timer reset - session will expire in 2 minutes if inactive');
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
                    return response.json().catch(() => null);
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
                    console.log('✅ Session extended - resetting timer');
                    this.resetInactivityTimer();
                } else {
                    console.log('❌ Failed to extend session');
                    this.autoLogout();
                }
            })
            .catch(error => {
                console.error('Error extending session:', error);
                this.autoLogout();
            });
        },

        autoLogout() {
            console.log('🔴 Auto logout - redirecting to login');
            if (this.inactivityTimer) clearInterval(this.inactivityTimer);
            if (this.pollingTimer) clearInterval(this.pollingTimer);
            
            // Crear formulario POST para logout
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
            console.log('👤 User clicked "Logout"');
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
    }));
});
