<?php

use App\Http\Controllers\ProfileController;
use App\Services\CajaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        $user = Auth::user();
        return redirect()->route($user->role);
    })->name('dashboard');

    // Ruta para mantener sesión activa
    Route::post('/session/ping', function () {
        return response()->json(['status' => 'ok']);
    })->name('session.ping');
});

Route::get('/admin', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'session.timeout', 'role:admin'])->name('admin');

Route::get('/supervisor', function () {
    return view('supervisor.dashboard');
})->middleware(['auth', 'session.timeout', 'role:supervisor'])->name('supervisor');

Route::middleware(['auth', 'session.timeout', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::resource('productos', \App\Http\Controllers\Supervisor\ProductoController::class);
    // Rutas para categorías (creables a nivel supervisor)
    Route::post('categorias', [\App\Http\Controllers\Supervisor\CategoriaController::class, 'store'])->name('categorias.store');
    Route::get('categorias/dropdown', [\App\Http\Controllers\Supervisor\CategoriaController::class, 'dropdown'])->name('categorias.dropdown');
    Route::get('categorias/all', [\App\Http\Controllers\Supervisor\CategoriaController::class, 'all'])->name('categorias.all');
    Route::patch('categorias/{categoria}', [\App\Http\Controllers\Supervisor\CategoriaController::class, 'update'])->name('categorias.update');
    Route::delete('categorias/{categoria}', [\App\Http\Controllers\Supervisor\CategoriaController::class, 'destroy'])->name('categorias.destroy');
    Route::resource('proveedores', \App\Http\Controllers\Supervisor\ProveedorController::class)
        ->parameters(['proveedores' => 'proveedor']);
    Route::resource('compras', \App\Http\Controllers\Supervisor\CompraController::class)
        ->parameters(['compras' => 'compra'])
        ->except(['edit', 'update']);
    Route::resource('clientes', \App\Http\Controllers\Supervisor\ClienteController::class)
        ->parameters(['clientes' => 'cliente']);

    Route::patch('compras/{compra}/cerrar', [\App\Http\Controllers\Supervisor\CompraController::class, 'cerrarCompra'])
        ->name('compras.cerrar');
    Route::patch('compras/{compra}/confirmar-recepcion', [\App\Http\Controllers\Supervisor\CompraController::class, 'confirmarRecepcion'])
        ->name('compras.confirmar-recepcion');
});

Route::middleware(['auth', 'session.timeout', 'role:bodeguero'])->prefix('bodeguero')->name('bodeguero.')->group(function () {
    Route::resource('recepciones', \App\Http\Controllers\Bodeguero\RecepcionController::class)
        ->parameters(['recepciones' => 'compra'])
        ->only(['index', 'show', 'update']);

    Route::get('historial', [\App\Http\Controllers\Bodeguero\HistorialController::class, 'index'])
        ->name('historial.index');
});

Route::get('/bodeguero', function () {
    return view('bodeguero.dashboard');
})->middleware(['auth', 'session.timeout', 'role:bodeguero'])->name('bodeguero');

Route::get('/cajero', function () {
    $user = Auth::user();
    $cajaAbierta = null;
    $cajaCerradaHoy = null;

    if ($user && $user->role === 'cajero') {
        $cajaService = new \App\Services\CajaService();
        $cajaAbierta = $cajaService->obtenerCajaAbierta();
        $cajaCerradaHoy = $cajaService->cajaCerradaHoy();
    }

    return view('cajero.dashboard', compact('cajaAbierta', 'cajaCerradaHoy'));
})->middleware(['auth', 'session.timeout', 'role:cajero'])->name('cajero');

Route::middleware(['auth', 'session.timeout', 'role:cajero'])->prefix('cajero')->name('cajero.')->group(function () {
    Route::get('caja', [\App\Http\Controllers\Cajero\CajaController::class, 'index'])
        ->name('caja.index');
    Route::get('caja/abrir', [\App\Http\Controllers\Cajero\CajaController::class, 'abrirForm'])
        ->name('caja.abrir.form');
    Route::post('caja/abrir', [\App\Http\Controllers\Cajero\CajaController::class, 'abrir'])
        ->name('caja.abrir');
    Route::get('caja/arqueo', [\App\Http\Controllers\Cajero\CajaController::class, 'arqueoForm'])
        ->name('caja.arqueo.form');
    Route::post('caja/arqueo', [\App\Http\Controllers\Cajero\CajaController::class, 'arqueo'])
        ->name('caja.arqueo');
    Route::get('caja/cierre', [\App\Http\Controllers\Cajero\CajaController::class, 'cierreForm'])
        ->name('caja.cierre.form');
    Route::post('caja/cerrar', [\App\Http\Controllers\Cajero\CajaController::class, 'cerrar'])
        ->name('caja.cerrar');

    Route::middleware('caja.abierta')->group(function () {
        Route::resource('ventas', \App\Http\Controllers\Cajero\VentaController::class);
        Route::get('creditos', [\App\Http\Controllers\Cajero\VentaController::class, 'listarCreditos'])->name('creditos.index');
        Route::patch('ventas/{venta}/pagar', [\App\Http\Controllers\Cajero\VentaController::class, 'registrarPago'])
            ->name('ventas.pagar');
        Route::get('ventas/{venta}/factura', [\App\Http\Controllers\Cajero\VentaController::class, 'factura'])
            ->name('ventas.factura');
        Route::get('ventas/{venta}/descargar-pdf', [\App\Http\Controllers\Cajero\VentaController::class, 'facturaPDF'])
            ->name('ventas.facturaPDF');

        Route::get('ventas/{venta}/devolucion', [\App\Http\Controllers\Cajero\DevolucionController::class, 'create'])
            ->name('ventas.devolucion.create');
        Route::post('devoluciones', [\App\Http\Controllers\Cajero\DevolucionController::class, 'store'])
            ->name('devoluciones.store');
        Route::get('devoluciones', [\App\Http\Controllers\Cajero\DevolucionController::class, 'index'])
            ->name('devoluciones.index');
        Route::get('devoluciones/{devolucion}', [\App\Http\Controllers\Cajero\DevolucionController::class, 'show'])
            ->name('devoluciones.show');
        Route::get('devoluciones/{devolucion}/descargar-pdf', [\App\Http\Controllers\Cajero\DevolucionController::class, 'downloadPDF'])
            ->name('devoluciones.downloadPDF');
    });
});

// Reportes - disponibles para supervisores y administradores
Route::middleware(['auth', 'session.timeout', 'role:supervisor,admin'])->prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [\App\Http\Controllers\ReporteController::class, 'index'])->name('index');
    Route::get('ventas', [\App\Http\Controllers\ReporteController::class, 'ventas'])->name('ventas');
    Route::get('inventario', [\App\Http\Controllers\ReporteController::class, 'inventario'])->name('inventario');
    Route::get('compras', [\App\Http\Controllers\ReporteController::class, 'compras'])->name('compras');
    Route::get('export/pdf', [\App\Http\Controllers\ReporteController::class, 'exportPDF'])->name('export.pdf');
    Route::get('export/excel', [\App\Http\Controllers\ReporteController::class, 'exportExcel'])->name('export.excel');
    Route::get('usuarios-por-sucursal/{sucursal_id}', [\App\Http\Controllers\ReporteController::class, 'getUsuariosPorSucursal'])->name('usuarios-por-sucursal');
});

// Chatbot - público para todos
Route::get('/chat', function () {
    return view('chat');
})->name('chat');
Route::post('/chat', [\App\Http\Controllers\ChatController::class, 'chat'])->name('chat.send');
Route::post('/chat/imagen', [\App\Http\Controllers\ChatController::class, 'buscarPorImagen'])->name('chat.imagen');

// Admin routes
Route::middleware(['auth', 'session.timeout', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', \App\Http\Controllers\Admin\AdminUserController::class);
    Route::resource('branches', \App\Http\Controllers\Admin\AdminBranchController::class);
    Route::resource('categorias', \App\Http\Controllers\Admin\CategoriaController::class);
    Route::resource('discounts', \App\Http\Controllers\Admin\AdminDiscountController::class);
    Route::get('settings', [\App\Http\Controllers\Admin\AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [\App\Http\Controllers\Admin\AdminSettingController::class, 'update'])->name('settings.update');
});

// Ruta de diagnóstico de sesión (solo para desarrollo)
if (app()->environment('local')) {
    Route::get('/session-diagnostics', function () {
        $user = Auth::user();
        $lastActivity = Session::get('last_activity');
        $currentTime = time();

        return response()->json([
            'authenticated' => Auth::check(),
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ] : null,
            'session' => [
                'id' => Session::getId(),
                'last_activity_timestamp' => $lastActivity,
                'last_activity_date' => $lastActivity ? date('Y-m-d H:i:s', $lastActivity) : 'No registrada',
                'current_time' => $currentTime,
                'current_time_date' => date('Y-m-d H:i:s', $currentTime),
                'elapsed_seconds' => $lastActivity ? ($currentTime - $lastActivity) : null,
                'timeout_seconds' => 120,
            ],
            'config' => [
                'session_lifetime' => config('session.lifetime'),
                'session_driver' => config('session.driver'),
                'session_expire_on_close' => config('session.expire_on_close'),
            ],
        ]);
    })->name('session.diagnostics');
}

require __DIR__.'/auth.php';
