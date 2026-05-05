<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n═══════════════════════════════════════════════════════════\n";
echo "  📊 DIAGNÓSTICO DETALLADO DE SESIÓN\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Obtener todas las sesiones
$sessions = DB::table('sessions')
    ->orderBy('last_activity', 'desc')
    ->get();

if ($sessions->isEmpty()) {
    echo "❌ No hay sesiones registradas.\n\n";
} else {
    echo "✅ Sesiones registradas en BD:\n";
    echo "─────────────────────────────────────────────────────────\n\n";
    
    foreach ($sessions as $i => $session) {
        $user_id = $session->user_id;
        $user = User::find($user_id);
        $lastActivity = $session->last_activity;
        $lastActivityDate = date('Y-m-d H:i:s', $lastActivity);
        $currentTime = time();
        $elapsedSeconds = $currentTime - $lastActivity;
        $elapsedMinutes = floor($elapsedSeconds / 60);
        $elapsedSecsRem = $elapsedSeconds % 60;

        echo "Sesión #" . ($i + 1) . "\n";
        echo "  ID Sesión: " . substr($session->id, 0, 16) . "...\n";
        echo "  Usuario: " . ($user ? "{$user->name} ({$user->role})" : "No encontrado") . "\n";
        echo "  Última Actividad: {$lastActivityDate}\n";
        echo "  Tiempo Transcurrido: {$elapsedMinutes}m {$elapsedSecsRem}s\n";
        
        if ($elapsedSeconds > 120) {
            echo "  ⚠️  ESTADO: Debería estar CERRADA (>120 segundos)\n";
            echo "  ✓ El middleware DEBERÍA haberla cerrado\n";
        } else {
            echo "  ✅ ESTADO: Activa (<120 segundos)\n";
        }
        echo "\n";
    }
}

echo "═══════════════════════════════════════════════════════════\n";
echo "  🔍 CONFIGURACIÓN ACTUAL\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$lifetime = config('session.lifetime');
echo "SESSION_LIFETIME (config): {$lifetime} minutos\n";
echo "SESSION_LIFETIME (segundos): " . ($lifetime * 60) . " segundos\n";

$driver = config('session.driver');
echo "SESSION_DRIVER: {$driver}\n";

$expireOnClose = config('session.expire_on_close');
echo "SESSION_EXPIRE_ON_CLOSE: " . ($expireOnClose ? 'true' : 'false') . "\n";

echo "\n═══════════════════════════════════════════════════════════\n";
echo "  📝 LOGS RECIENTES (últimas 15 líneas)\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = array_slice(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -15);
    
    foreach ($logs as $log) {
        // Colorear los logs importantes
        if (strpos($log, 'Session timeout') !== false) {
            echo "  ❌ " . $log . "\n";
        } elseif (strpos($log, 'Session activity updated') !== false) {
            echo "  ✅ " . $log . "\n";
        } else {
            echo "  " . $log . "\n";
        }
    }
} else {
    echo "❌ Archivo de log no encontrado\n";
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "  📋 INSTRUCCIONES DE PRUEBA\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "1. ABRE TU NAVEGADOR Y REALIZA ESTAS ACCIONES:\n";
echo "   a) Ve a: http://localhost/login\n";
echo "   b) Inicia sesión (usa cualquier usuario)\n";
echo "   c) Una vez en el dashboard, NO HAGAS NADA\n";
echo "   d) Espera exactamente 2 minutos 5 segundos\n";
echo "   e) Haz clic o recarga la página\n\n";

echo "2. MIENTRAS ESPERAS, EJECUTA ESTE SCRIPT NUEVAMENTE:\n";
echo "   php diagnostico_sesion.php\n\n";

echo "3. DESPUÉS DE 2+ MINUTOS DE INACTIVIDAD:\n";
echo "   ✓ Deberías ver error 404 o ser redirigido a login\n";
echo "   ✓ Este script mostrará: 'Debería estar CERRADA'\n";
echo "   ✓ Los logs mostrarán: 'Session timeout - User logout'\n\n";

echo "═══════════════════════════════════════════════════════════\n\n";
    ->get();

if ($sessions->isEmpty()) {
    echo "❌ No hay sesiones registradas en la base de datos.\n\n";
} else {
    echo "✅ Últimas 5 sesiones:\n";
    echo "─────────────────────────────────────────────────────────\n";
    
    foreach ($sessions as $session) {
        $user_id = $session->user_id;
        $user = User::find($user_id);
        $lastActivity = date('Y-m-d H:i:s', $session->last_activity);
        $elapsedSeconds = time() - $session->last_activity;
        $elapsedMinutes = floor($elapsedSeconds / 60);
        $elapsedSecondsRemainder = $elapsedSeconds % 60;

        echo "\nSesión ID: " . substr($session->id, 0, 8) . "...\n";
        echo "Usuario: " . ($user ? $user->name . " ({$user->role})" : "Usuario {$user_id} (No encontrado)") . "\n";
        echo "Última actividad: {$lastActivity}\n";
        echo "Tiempo transcurrido: {$elapsedMinutes}:{$elapsedSecondsRemainder}s\n";
        
        if ($elapsedSeconds > 120) {
            echo "⚠️  ESTADO: Debería estar cerrada (>2 min inactiva)\n";
        } else {
            echo "✅ ESTADO: Activa\n";
        }
    }
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "  🔍 CONFIGURACIÓN DE SESSION\n";
echo "═══════════════════════════════════════════════════════════\n";

$lifetime = config('session.lifetime');
echo "SESSION_LIFETIME (config): {$lifetime} minutos\n";

$envLifetime = env('SESSION_LIFETIME', 'No configurado');
echo "SESSION_LIFETIME (.env): {$envLifetime} minutos\n";

$expireOnClose = config('session.expire_on_close');
echo "SESSION_EXPIRE_ON_CLOSE: " . ($expireOnClose ? 'true' : 'false') . "\n";

echo "\n═══════════════════════════════════════════════════════════\n";
echo "  📝 LOGS RECIENTES\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = array_slice(file($logFile, FILE_IGNORE_NEW_LINES), -20);
    
    $sessionLogs = array_filter($logs, function($line) {
        return strpos($line, 'Session') !== false || 
               strpos($line, 'session') !== false ||
               strpos($line, 'timeout') !== false ||
               strpos($line, 'activity') !== false;
    });

    if (empty($sessionLogs)) {
        echo "ℹ️  No hay logs de sesión en los últimos 20 registros.\n";
    } else {
        echo "✅ Logs relevantes encontrados:\n";
        foreach ($sessionLogs as $log) {
            echo "  " . trim($log) . "\n";
        }
    }
} else {
    echo "❌ Archivo de log no encontrado: {$logFile}\n";
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "  ✨ PRÓXIMOS PASOS:\n";
echo "═══════════════════════════════════════════════════════════\n\n";
echo "1. Inicia sesión en el navegador\n";
echo "2. Espera 2 minutos SIN hacer nada\n";
echo "3. Ejecuta este script nuevamente para ver cambios\n";
echo "4. Verifica que la sesión sea cerrada y redirigida a login\n\n";
