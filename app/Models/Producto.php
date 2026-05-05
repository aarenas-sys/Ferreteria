<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'precio',
        'stock',
        'stock_minimo',
        'sucursal_id',
        'categoria_id',
        'imagen',
        'image_hash',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
    ];

    /**
     * Relación: Un producto pertenece a una sucursal
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'sucursal_id');
    }

    /**
     * Relación: Un producto pertenece a una categoría
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Relación: Un producto tiene muchos detalles de compra
     */
    public function compraDetalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class, 'producto_id');
    }

    /**
     * Obtener el estado del stock de forma dinámica
     * normal: stock >= 20
     * bajo: stock < 20
     */
    public function getEstadoStockAttribute(): string
    {
        return $this->stock >= 20 ? 'normal' : 'bajo';
    }

    /**
     * Obtener el color del estado del stock
     * normal → green
     * bajo → red
     */
    public function getEstadoStockColorAttribute(): string
    {
        return $this->estado_stock === 'normal' ? 'green' : 'red';
    }

    /**
     * Obtener el precio de compra (del último compra detalle)
     */
    public function getUltimoPrecioCompraAttribute(): ?float
    {
        return $this->compraDetalles()
            ->latest('created_at')
            ->first()
            ?->precio_compra ?? null;
    }

    /**
     * Obtener el precio de venta (alias para claridad)
     */
    public function getPrecioVentaAttribute()
    {
        return $this->precio;
    }

    /**
     * Scope: Productos con stock bajo (< 20)
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock < 20');
    }

    /**
     * Scope: Productos con stock normal (>= 20)
     */
    public function scopeNormalStock($query)
    {
        return $query->whereRaw('stock >= 20');
    }

    /**
     * Scope: Filtrar por categoría
     */
    public function scopeByCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    /**
     * Scope: Filtrar por estado de stock
     */
    public function scopeByEstadoStock($query, $estado)
    {
        if ($estado === 'bajo') {
            return $query->whereRaw('stock < 20');
        } elseif ($estado === 'normal') {
            return $query->whereRaw('stock >= 20');
        }
        return $query;
    }

    /**
     * Scope: Búsqueda por nombre, código o descripción
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
              ->orWhere('codigo', 'like', "%{$search}%")
              ->orWhere('descripcion', 'like', "%{$search}%");
        });
    }

    /**
     * Verificar si está bajo stock (usando stock_minimo)
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }
}
