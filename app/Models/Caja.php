<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = [
        'usuario_id',
        'sucursal_id',
        'monto_inicial',
        'total_sistema',
        'monto_real',
        'diferencia',
        'fecha_apertura',
        'fecha_cierre',
        'estado',
    ];

    protected $casts = [
        'monto_inicial' => 'decimal:2',
        'total_sistema' => 'decimal:2',
        'monto_real' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'sucursal_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'caja_id');
    }

    /**
     * Calcular el total del sistema según la fórmula:
     * total_sistema = monto_inicial + ventas_contado + pagos_credito - devoluciones
     */
    public function calcularTotalSistema(): float
    {
        $ventasContado = $this->movimientos()->where('tipo', 'venta_contado')->sum('monto');
        $pagosCredito = $this->movimientos()->where('tipo', 'pago_credito')->sum('monto');
        $devoluciones = $this->movimientos()->where('tipo', 'devolucion')->sum('monto');

        return (float) (
            $this->monto_inicial +
            $ventasContado +
            $pagosCredito -
            $devoluciones
        );
    }

    /**
     * Verificar si hay saldo suficiente para una devolución
     */
    public function tieneBalanceSuficiente(float $monto): bool
    {
        return $this->calcularTotalSistema() >= $monto;
    }

    /**
     * Obtener el detalle de cálculo
     */
    public function obtenerDetalleTotales(): array
    {
        $ventasContado = $this->movimientos()->where('tipo', 'venta_contado')->sum('monto');
        $pagosCredito = $this->movimientos()->where('tipo', 'pago_credito')->sum('monto');
        $devoluciones = $this->movimientos()->where('tipo', 'devolucion')->sum('monto');
        $totalSistema = $this->calcularTotalSistema();

        return [
            'monto_inicial' => (float) $this->monto_inicial,
            'ventas_contado' => (float) $ventasContado,
            'pagos_credito' => (float) $pagosCredito,
            'devoluciones' => (float) $devoluciones,
            'total_sistema' => $totalSistema,
        ];
    }
}
