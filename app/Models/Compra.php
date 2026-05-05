<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'proveedor_id',
        'user_id',
        'sucursal_id',
        'tipo_pago',
        'estado',
        'total_estimado',
        'total_real',
        'observacion_cierre',
        'fecha_cierre',
        'fecha_recepcion',
    ];

    protected $casts = [
        'total_estimado' => 'decimal:2',
        'total_real' => 'decimal:2',
        'fecha_cierre' => 'datetime',
        'fecha_recepcion' => 'datetime',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'sucursal_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('sucursal_id', $branchId);
    }

    public function isPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function isParcial(): bool
    {
        return $this->estado === 'parcial';
    }

    public function isPendienteConfirmacion(): bool
    {
        return $this->estado === 'pendiente_confirmacion';
    }

    public function isRecibida(): bool
    {
        return $this->estado === 'recibida';
    }

    public function updateEstadoFromDetails(): void
    {
        if ($this->detalles->every(fn ($detalle) => $detalle->cantidad_recibida === 0)) {
            $this->estado = 'pendiente';
            return;
        }

        if ($this->detalles->every(fn ($detalle) => $detalle->cantidad_recibida >= $detalle->cantidad_solicitada)) {
            $this->estado = 'pendiente_confirmacion';
            return;
        }

        $this->estado = 'parcial';
    }

    public function recalculateTotalReal(): void
    {
        $this->total_real = $this->detalles->sum(fn ($detalle) => $detalle->cantidad_recibida * $detalle->precio_compra);
    }
}
