# 🧪 Test de Session Timeout - Validación Completa

## ✅ Cambios Realizados

### 1. Z-Index del Modal
**Archivo**: `resources/views/components/session-timeout-alert.blade.php`
- ✅ Aumentado z-index de `z-50` a `z-[9999]`
- ✅ El modal ahora se superpone sobre el ícono del chatbot

### 2. Polling más Frecuente
**Archivos**: 
- `resources/views/components/session-timeout-alert.blade.php`
- `resources/js/session-timeout.js`

**Cambios**:
- ✅ Reducido intervalo de polling de 30 segundos a **10 segundos**
- ✅ Ahora verifica la sesión cada 10 segundos incluso sin interacción
- ✅ Funciona en segundo plano aunque no estés en la pestaña
- ✅ Mejor manejo de errores de red

---

## 🧪 Procedimiento de Prueba

### Escenario 1: Session Timeout Con Interacción Visible

**Tiempo**: ~2.5 minutos

1. Abre tu navegador en http://localhost:8000
2. Asegúrate de estar autenticado
3. **NO HAGAS NADA** - no muevas el mouse, no hagas click
4. Observa en la consola del navegador (F12 > Console):
   ```
   🟢 Session timeout monitoring initialized
   ⏱️ Inactivity: 10s
   ⏱️ Inactivity: 20s
   ...
   ⏱️ Inactivity: 90s
   ⚠️ Session warning - showing alert
   ```
5. Después de 2 minutos (120s), verás el modal de sesión expirada
6. El modal debe **aparecer sobre el ícono del chatbot** (verificar z-index)

**Esperado**: 
- ✅ Modal superpuesto sobre el chatbot
- ✅ Barra de progreso mostrando tiempo restante
- ✅ Auto-logout después de 30 segundos (en la alerta)

---

### Escenario 2: Validar Heartbeat en Segundo Plano (IMPORTANTE)

**Tiempo**: ~3 minutos

1. Abre tu navegador en http://localhost:8000
2. Abre la Consola del Navegador (F12 > Console)
3. Haz que tu navegador pierda el foco o cambia a otra pestaña
4. **Observa la consola cada 10 segundos** - deberías ver:
   ```
   🔄 Polling session status (every 10s)...
   ✅ Session still valid
   ```
5. Déjalo así por 2+ minutos **SIN hacer nada en la pestaña actual**
6. El polling debe continuar ejecutándose cada 10 segundos
7. Después de 2 minutos de inactividad total, debería:
   - Mostrar la alerta de sesión por expirar
   - O automáticamente hacer logout si pasó el timeout total

**Esperado**:
- ✅ El polling continúa ejecutándose cada 10 segundos en segundo plano
- ✅ Las líneas `🔄 Polling session status` aparecen en la consola
- ✅ Aunque la pestaña NO tenga foco, el sistema sigue contando inactividad

---

### Escenario 3: Interrupción del Polling (Conozca qué pasa si la sesión ya expiró en el servidor)

**Tiempo**: ~2.5 minutos

1. Abre tu navegador en http://localhost:8000
2. Abre la Consola del Navegador (F12 > Console)
3. **NO HAGAS NADA** - déjalo por ~2.5 minutos
4. En algún punto verás:
   ```
   🔴 Server returned 401/419 - session expired on server
   ```
5. Automáticamente debería hacer logout y llevarte a `/login`

**Esperado**:
- ✅ El servidor detecto la sesión expirada
- ✅ Se ejecutó auto-logout automáticamente
- ✅ Fuiste redirigido a la página de login

---

### Escenario 4: Recuperar Sesión Haciendo Click en "Continuar Sesión"

**Tiempo**: ~2.5 minutos

1. Abre tu navegador en http://localhost:8000
2. **NO HAGAS NADA** por ~90 segundos
3. Verás la alerta de "Tu sesión está por expirar" con botones
4. Haz click en **"Continuar sesión"**
5. En la consola verás:
   ```
   ✅ User clicked "Continue session"
   ✅ Session extended - resetting timer
   ```
6. El contador se reinicia y tienes otros 2 minutos

**Esperado**:
- ✅ El botón funciona correctamente
- ✅ El timer se reinicia
- ✅ Puedes continuar trabajando

---

### Escenario 5: Modal se Superpone Sobre el Chatbot

**Tiempo**: ~2.5 minutos

1. Abre http://localhost:8000
2. **Abre el chat** haciendo click en el ícono 💬 de la esquina inferior derecha
3. **NO HAGAS NADA** por 2 minutos (mantén el chat abierto)
4. Cuando aparezca la alerta de sesión expirada, verás:
   - ✅ El modal está **encima** del chat (no está detrás)
   - ✅ Los botones "Continuar sesión" y "Cerrar sesión" son clickeables
   - ✅ La barra de progreso es visible

**Esperado**:
- ✅ Z-index correcto (modal sobre chatbot)
- ✅ Interacción con el modal funciona normalmente

---

## 📊 Resumen de Cambios

| Item | Antes | Después | Beneficio |
|------|-------|---------|-----------|
| Z-index Modal | z-50 | z-[9999] | Se superpone sobre chatbot |
| Polling | Cada 30s | Cada 10s | Detección más rápida de sesión expirada |
| Heartbeat en fondo | ❌ No confiable | ✅ Confiable cada 10s | Funciona sin interacción visible |
| Timeout sin pestaña activa | ❌ No funcionaba bien | ✅ Funciona correctamente | Logout automático incluso en segundo plano |

---

## 🔍 Cómo Debuggear si Hay Problemas

### Abre la Consola del Navegador (F12)

**Busca estos logs**:

```javascript
// ESPERADO - Se ejecuta una sola vez al cargar la página
🟢 Session timeout monitoring initialized

// ESPERADO - Se ejecuta cada segundo (contador de inactividad)
⏱️ Inactivity: 10s
⏱️ Inactivity: 20s
...

// ESPERADO - Se ejecuta cada 10 segundos en segundo plano
🔄 Polling session status (every 10s)...
✅ Session still valid

// CUANDO LA SESIÓN ESTÁ POR EXPIRAR (90 segundos de inactividad)
⚠️ Session warning - showing alert

// SI LA SESIÓN YA EXPIRÓ EN EL SERVIDOR
🔴 Server returned 401/419 - session expired on server

// AUTO-LOGOUT AUTOMÁTICO
🔴 Auto logout - redirecting to login
```

---

## ⚠️ Notas Importantes

1. **SESSION_LIFETIME = 2 minutos**: Configurado en `.env`
   - Este es el tiempo total de inactividad antes de logout
   - No es el tiempo del servidor - es el tiempo del cliente

2. **Polling cada 10 segundos**: Verifica con el servidor si la sesión sigue válida
   - Si el servidor dice que NO (401/419), se ejecuta auto-logout

3. **Modal siempre encima**: Z-index de 9999 asegura que siempre esté visible
   - Ni el chatbot ni otros elementos pueden ocultarlo

4. **Funciona en segundo plano**: 
   - Aunque cambies de pestaña
   - Aunque minimices el navegador
   - El contador de inactividad sigue aumentando
   - El polling sigue verificando cada 10 segundos

---

## ✅ Si Todo Funciona Correctamente

- [ ] Modal aparece sobre el chatbot
- [ ] Inactividad se cuenta aunque no estés en la pestaña
- [ ] Polling ocurre cada 10 segundos (visible en consola)
- [ ] Auto-logout después de 2 minutos de inactividad
- [ ] "Continuar sesión" funciona correctamente
- [ ] Barra de progreso es visible y cuenta hacia atrás

