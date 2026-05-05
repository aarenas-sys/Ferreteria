<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'compra_id',
        'producto_id',
        'cantidad_solicitada',
        'cantidad_recibida',
        'precio_compra',
        'subtotal',
    ];

    protected $casts = [
        'cantidad_solicitada' => 'integer',
        'cantidad_recibida' => 'integer',
        'precio_compra' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function getPendienteAttribute(): int
    {
        return $this->cantidad_solicitada - $this->cantidad_recibida;
    }
}
