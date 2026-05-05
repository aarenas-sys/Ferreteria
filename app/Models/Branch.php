<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = ['name', 'address', 'phone'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'sucursal_id');
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'descuento_sucursal', 'sucursal_id', 'descuento_id');
    }
}
