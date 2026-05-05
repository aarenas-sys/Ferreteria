<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'documento',
        'email',
        'telefono',
        'direccion',
        'cupo_credito',
        'saldo_actual',
        'estado_credito',
    ];

    protected $casts = [
        'cupo_credito' => 'decimal:2',
        'saldo_actual' => 'decimal:2',
    ];

    // Accessor para nombre completo
    protected function nombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->primer_nombre} {$this->segundo_nombre} {$this->primer_apellido} {$this->segundo_apellido}")
        );
    }

    public function creditos(): HasMany
    {
        return $this->hasMany(\App\Models\Credito::class);
    }

    public function actualizarEstadoCredito(): void
    {
        if ($this->saldo_actual <= 0) {
            if ($this->estado_credito !== 'bloqueado') {
                $this->estado_credito = 'activo';
            }
        } else {
            $tieneVencidos = $this->creditos()
                ->where('estado', 'pendiente')
                ->whereDate('fecha_vencimiento', '<', now())
                ->exists();

            if ($tieneVencidos) {
                $this->estado_credito = 'mora';
            } elseif ($this->estado_credito !== 'bloqueado') {
                $this->estado_credito = 'activo';
            }
        }

        $this->save();
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado_credito', 'activo');
    }

    public function scopeEnMora($query)
    {
        return $query->where('estado_credito', 'mora');
    }

    public function scopeBloqueados($query)
    {
        return $query->where('estado_credito', 'bloqueado');
    }

    // Métodos de estado
    public function estaActivo(): bool
    {
        return $this->estado_credito === 'activo';
    }

    public function estaEnMora(): bool
    {
        return $this->estado_credito === 'mora';
    }

    public function estaBloqueado(): bool
    {
        return $this->estado_credito === 'bloqueado';
    }
}
