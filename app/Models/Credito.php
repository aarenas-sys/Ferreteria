<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Credito extends Model
{
    protected $fillable = [
        'venta_id',
        'cliente_id',
        'monto_total',
        'saldo_pendiente',
        'fecha_inicio',
        'fecha_vencimiento',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
        'monto_total' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function registrarPago(float $monto): void
    {
        $this->saldo_pendiente = max(0, $this->saldo_pendiente - $monto);

        if ($this->saldo_pendiente <= 0) {
            $this->estado = 'pagado';
        }

        $this->save();
    }
}
