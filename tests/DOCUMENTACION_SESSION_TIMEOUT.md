# 📋 Documentación: Expiración de Sesión por Inactividad

## 🎯 Objetivo
Cerrar automáticamente la sesión del usuario si está inactivo durante **2 minutos**.

---

## ✅ Cambios Implementados

### 1. **Configuración de Sesión** (Backend)

#### Archivos modificados:
- `.env` - `SESSION_LIFETIME=2` (2 minutos)
- `config/session.php` - Lifetime por defecto a 2 minutos
- `bootstrap/app.php` - Middleware registrado globalmente

#### Configuración:
```env
SESSION_DRIVER=database          # Usa BD para persistencia
SESSION_LIFETIME=2               # 2 minutos de inactividad
SESSION_ENCRYPT=false            # No encripta sesión
SESSION_EXPIRE_ON_CLOSE=false    # Persiste aunque cierre navegador
```

---

### 2. **Middleware de Validación** (Backend)

#### Archivo: `app/Http/Middleware/CheckSessionTimeout.php`

**Características:**
- ✅ Verifica tiempo de inactividad cada solicitud
- ✅ Registra `last_activity` en sesión
- ✅ Compara tiempo transcurrido vs threshold (2 min)
- ✅ Logout automático si expira
- ✅ Redirige a login con mensaje

**Flujo:**
```
Usuario hace solicitud
    ↓
¿Está autenticado?
    ├─ SÍ → Verificar last_activity
    │        ├─ ¿Pasó 2 min sin actividad?
    │        │  ├─ SÍ → Logout + Redirigir a login
    │        │  └─ NO → Actualizar last_activity
    │        └─ Continuar solicitud
    └─ NO → Continuar solicitud
```

**Registrado en:**
```php
// bootstrap/app.php
$middleware->web(append: [
    \App\Http\Middleware\CheckSessionTimeout::class,
]);
```

---

### 3. **Detección Frontend de Inactividad** (JavaScript/Alpine.js)

#### Archivo: `resources/views/components/session-timeout-alert.blade.php`

**Características:**
- ✅ Detecta eventos: `mousemove`, `keydown`, `click`, `touchstart`
- ✅ Contador de inactividad en tiempo real
- ✅ Alerta visual 30 segundos antes de expirar
- ✅ Barra de progreso animada
- ✅ Botón "Continuar sesión" para extender tiempo
- ✅ Botón "Cerrar sesión" para logout voluntario

**Timers:**
```javascript
Total de inactividad: 120 segundos (2 minutos)
Mostrar alerta en: 90 segundos
Tiempo para decidir: 30 segundos
Logout automático: 120 segundos
```

**Interacciones detectadas:**
```javascript
- mousemove   → Reinicia contador
- keydown     → Reinicia contador
- click       → Reinicia contador
- touchstart  → Reinicia contador (móvil)
```

---

### 4. **Ruta para Mantener Sesión Activa**

#### Archivo: `routes/web.php`

```php
Route::post('/session/ping', function () {
    return response()->json(['status' => 'ok']);
})->name('session.ping');
```

**Uso:**
- Al hacer click en "Continuar sesión", se envía POST a esta ruta
- Actualiza `last_activity` en el servidor
- Frontend reinicia contador de inactividad
- Sesión se mantiene activa

---

### 5. **Interfaz Usuario (UX)**

#### Componente: `x-session-timeout-alert`

**Ubicación:** Esquina inferior derecha

**Estados:**
1. **Normal** → No visible, detecta inactividad
2. **Alerta** → Visible 30 segundos antes de expirar
3. **Progreso** → Barra animada mostrando tiempo restante

**Elementos:**
```
┌─────────────────────────────────────┐
│ ⏱️ Tu sesión está por expirar       │
│                                     │
│ Expirará en: 0:28                   │
│                                     │
│ ▓▓▓▓░░░░░░░░░░░░░░░░░░░░░░         │ (barra progreso)
│                                     │
│ [Continuar sesión] [Cerrar sesión] │
└─────────────────────────────────────┘
```

---

## 🔒 Seguridad

### Validación en Múltiples Niveles

1. **Backend (Servidor)**
   - Middleware valida cada solicitud
   - Verifica tiempo de inactividad
   - Destruye sesión si expiró
   - Logout + destrucción de tokens CSRF

2. **Frontend (Cliente)**
   - Detecta inactividad localmente
   - Alerta visual previa
   - Opción de continuar o cerrar
   - Logout elegante

3. **Rutas Protegidas**
   ```php
   Route::middleware(['auth', 'role:cajero'])->group(...)
   ```
   - Todas las rutas verifican autenticación
   - Redirección a login si no está autenticado
   - Incompatible con sesión expirada

---

## 🧪 Pruebas

### Test 1: Verificar Inactividad Backend
```bash
# 1. Iniciar sesión en el navegador
# 2. Abre consola del navegador (F12)
# 3. Espera 2 minutos SIN hacer clic, movimiento o escribir
# 4. Resultado: Se redirige a login automáticamente
```

### Test 2: Verificar Alerta Frontend
```bash
# 1. Iniciar sesión
# 2. No hacer nada por 1 minuto 30 segundos
# 3. Resultado: Aparece alerta en esquina inferior derecha
# 4. Muestra tiempo restante: ~30 segundos
# 5. Barra de progreso animada
```

### Test 3: Continuar Sesión
```bash
# 1. Esperar a que aparezca alerta
# 2. Hacer clic en "Continuar sesión"
# 3. Resultado: Alerta desaparece
# 4. Contador reinicia desde 0
# 5. Sesión se mantiene activa
```

### Test 4: Logout Manual
```bash
# 1. Mientras hay alerta visible
# 2. Hacer clic en "Cerrar sesión"
# 3. Resultado: Redirige a login inmediatamente
# 4. Sesión destruida correctamente
```

### Test 5: Verificar en Diferentes Módulos
```bash
# 1. Login como admin → Inactividad → Logout
# 2. Login como cajero → Inactividad → Logout
# 3. Login como bodeguero → Inactividad → Logout
# 4. Login como supervisor → Inactividad → Logout
```

---

## 📱 Compatibilidad

✅ **Navegadores Soportados:**
- Chrome/Chromium 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Opera 76+

✅ **Dispositivos:**
- Desktop (Windows, Mac, Linux)
- Tablet
- Móvil

✅ **Frameworks Laravel:**
- Laravel 11+ ✅
- Mantiene compatibilidad con versiones previas

---

## 🛠️ Archivos Modificados

```
.env                                           # SESSION_LIFETIME=2
├── config/session.php                         # lifetime default 2
├── bootstrap/app.php                          # Middleware registrado
├── app/Http/Middleware/CheckSessionTimeout.php # Nuevo middleware
├── routes/web.php                             # Ruta /session/ping
├── resources/views/layouts/app.blade.php      # Componente incluido
└── resources/views/components/
    └── session-timeout-alert.blade.php        # Nuevo componente
```

---

## ⚙️ Configuración Avanzada

### Cambiar Tiempo de Inactividad

**Opción 1: Vía .env (Recomendado)**
```env
SESSION_LIFETIME=5  # 5 minutos en lugar de 2
```

**Opción 2: En componente**
```javascript
totalTimeout: 120000,        // 2 minutos en milisegundos
warningThreshold: 30000,     // Alerta 30 seg antes
```

### Cambiar Tiempo de Alerta

En `session-timeout-alert.blade.php`:
```javascript
warningThreshold: 60000,  // Mostrar alerta 60 seg antes (en lugar de 30)
```

### Deshabilitar Alerta Visual (Solo Logout Backend)

Reemplaza componente en `app.blade.php`:
```php
<!-- Comentar esta línea -->
<!-- <x-session-timeout-alert /> -->
```

---

## 🐛 Troubleshooting

### El usuario no ve la alerta
✓ Verificar que `.env` tenga `SESSION_LIFETIME=2`
✓ Limpiar cache: `php artisan config:cache`
✓ Verificar que Alpine.js está cargado en `app.js`
✓ Abrir consola (F12) para verificar errores

### La sesión no expira
✓ Middleware aplicado a rutas web: `$middleware->web(append: [...])`
✓ Verificar que usuario está `@auth`
✓ Comprobar que `session.ping` retorna 200 OK

### El logout no funciona
✓ Verificar CSRF token en html: `<meta name="csrf-token">`
✓ Revisar que ruta `logout` existe: `routes/auth.php`
✓ Comprobar permisos de sesión en BD

---

## 📊 Monitoreo

### Verificar Sesiones Activas

```bash
# En base de datos
SELECT user_id, last_activity, expires FROM sessions WHERE user_id = 1;

# En archivo de sesiones
ls -la storage/framework/sessions/
```

### Logs de Inactividad

Los eventos de logout por inactividad se registran en logs de Laravel:
```bash
tail -f storage/logs/laravel.log | grep "inactividad"
```

---

## ✨ Beneficios

✅ **Seguridad**
- Cierre automático de sesiones abandonadas
- Protección contra acceso no autorizado en PCs compartidas

✅ **Experiencia Usuario**
- Alerta previa antes de cerrar sesión
- Opción de continuar sin perder progreso
- Logout elegante, no sorpresivo

✅ **Cumplimiento**
- Cumple con políticas de seguridad corporativas
- Ideal para sistemas financieros (FerreNet)

✅ **Integridad Sistema**
- No rompe autenticación existente
- Compatible con roles
- Funciona en todos los módulos

---

## 📞 Soporte

Si tienes preguntas sobre la implementación:
1. Revisar logs: `storage/logs/laravel.log`
2. Verificar consola navegador: F12 → Console
3. Revisar BD sesiones: tabla `sessions`

**Fecha implementación:** 1 de Mayo de 2026
**Versión:** 1.0
**Status:** ✅ Productivo
