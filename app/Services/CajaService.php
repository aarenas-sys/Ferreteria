<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\MovimientoCaja;
use Illuminate\Support\Facades\Auth;

class CajaService
{
    public function obtenerCajaAbierta(): ?Caja
    {
        $user = Auth::user();

        return Caja::where('usuario_id', $user->id)
            ->where('sucursal_id', $user->branch_id)
            ->where('estado', 'abierta')
            ->first();
    }

    public function cajaCerradaHoy(): ?Caja
    {
        $user = Auth::user();

        return Caja::where('usuario_id', $user->id)
            ->where('sucursal_id', $user->branch_id)
            ->where('estado', 'cerrada')
            ->whereDate('fecha_apertura', today())
            ->first();
    }

    public function abrirCaja(float $montoInicial): Caja
    {
        $user = Auth::user();

        if ($this->obtenerCajaAbierta()) {
            throw new \Exception('Ya existe una caja abierta para este cajero y sucursal.');
        }

        // Validar que no se haya abierto una caja hoy
        $cajaHoy = Caja::where('usuario_id', $user->id)
            ->where('sucursal_id', $user->branch_id)
            ->whereDate('fecha_apertura', today())
            ->first();

        if ($cajaHoy) {
            throw new \Exception('Ya se abrió una caja hoy. Solo se permite una caja por día.');
        }

        return Caja::create([
            'usuario_id' => $user->id,
            'sucursal_id' => $user->branch_id,
            'monto_inicial' => $montoInicial,
            'fecha_apertura' => now(),
            'estado' => 'abierta',
        ]);
    }

    public function registrarMovimiento(string $tipo, float $monto, ?int $referenciaId = null, ?string $descripcion = null): MovimientoCaja
    {
        $caja = $this->obtenerCajaAbierta();

        if (! $caja) {
            throw new \Exception('No hay una caja abierta en esta sucursal.');
        }

        return MovimientoCaja::create([
            'caja_id' => $caja->id,
            'tipo' => $tipo,
            'monto' => $monto,
            'referencia_id' => $referenciaId,
            'descripcion' => $descripcion,
        ]);
    }

    public function calcularTotales(Caja $caja): array
    {
        return $caja->obtenerDetalleTotales();
    }

    /**
     * Validar si se puede realizar una devolución
     */
    public function validarDevolucion(Caja $caja, float $montoDevolucion): array
    {
        $totalActual = $caja->calcularTotalSistema();

        if ($totalActual < $montoDevolucion) {
            return [
                'valido' => false,
                'mensaje' => sprintf(
                    'No hay saldo suficiente en caja para realizar la devolución. Saldo disponible: $%.2f | Monto a devolver: $%.2f',
                    $totalActual,
                    $montoDevolucion
                ),
                'saldo_disponible' => $totalActual,
            ];
        }

        return [
            'valido' => true,
            'mensaje' => 'Saldo suficiente en caja',
            'saldo_disponible' => $totalActual - $montoDevolucion,
        ];
    }

    public function arquearCaja(Caja $caja, float $montoReal): Caja
    {
        $totalSistema = $caja->calcularTotalSistema();

        $caja->update([
            'total_sistema' => $totalSistema,
            'monto_real' => $montoReal,
            'diferencia' => $montoReal - $totalSistema,
        ]);

        return $caja;
    }

    public function cerrarCaja(Caja $caja): Caja
    {
        if ($caja->estado !== 'abierta') {
            throw new \Exception('La caja ya está cerrada.');
        }

        // Validar que la caja pertenece al usuario actual
        $user = Auth::user();
        if ($caja->usuario_id !== $user->id || $caja->sucursal_id !== $user->branch_id) {
            throw new \Exception('No tienes permiso para cerrar esta caja.');
        }

        if ($caja->total_sistema === null || $caja->monto_real === null) {
            $this->arquearCaja($caja, $caja->monto_real ?? 0);
        }

        $caja->update([
            'fecha_cierre' => now(),
            'estado' => 'cerrada',
        ]);

        return $caja;
    }
}
