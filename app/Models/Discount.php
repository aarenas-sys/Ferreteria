<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'value',
        'active',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'active' => 'boolean',
        'value' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'descuento_sucursal', 'descuento_id', 'sucursal_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('active', true)
            ->where(function (Builder $query) {
                $query->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now());
            });
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->whereHas('branches', function (Builder $query) use ($branchId) {
            $query->where('branches.id', $branchId);
        });
    }

    public static function availableForBranch(int $branchId)
    {
        return self::active()->forBranch($branchId)->get();
    }
}
