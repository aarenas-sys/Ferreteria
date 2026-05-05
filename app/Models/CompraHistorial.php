<?php

namespace App\Models;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraHistorial extends Model
{
    use HasFactory;

    protected $table = 'compra_historiales';

    protected $fillable = [
        'compra_id',
        'user_id',
        'compra_detalle_id',
        'accion',
        'descripcion',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalle(): BelongsTo
    {
        return $this->belongsTo(CompraDetalle::class, 'compra_detalle_id');
    }
}
