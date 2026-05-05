<?php

namespace App\Console\Commands;

use App\Models\Caja;
use App\Models\User;
use Illuminate\Console\Command;

class PruebaCaja extends Command
{
    protected $signature = 'app:prueba-caja {--cerrar}';
    protected $description = 'Crea un registro de caja de prueba para verificar que la hora se guarde correctamente';

    public function handle(): int
    {
        try {
            // Obtener el primer usuario con rol cajero
            $cajero = User::where('role', 'cajero')->first();

            if (!$cajero) {
                $this->error('No hay usuarios con rol de cajero');
                return 1;
            }

            if ($this->option('cerrar')) {
                // Cerrar la caja abierta
                $cajaAbierta = Caja::where('usuario_id', $cajero->id)
                    ->where('estado', 'abierta')
                    ->first();

                if (!$cajaAbierta) {
                    $this->error('No hay caja abierta para cerrar');
                    return 1;
                }

                $cajaAbierta->update([
                    'total_sistema' => 1000,
                    'monto_real' => 1000,
                    'diferencia' => 0,
                    'fecha_cierre' => now(),
                    'estado' => 'cerrada',
                ]);

                $this->info('✓ Caja cerrada correctamente');
                $this->line('');
                $this->line('📊 DETALLES DEL CIERRE:');
                $this->line('─────────────────────────────────');
                $this->line("Cajero: {$cajaAbierta->usuario->name}");
                $this->line("Sucursal: {$cajaAbierta->sucursal->name ?? 'Principal'}");
                $this->line("Apertura: {$cajaAbierta->fecha_apertura->format('d/m/Y H:i:s')}");
                $this->line("Cierre: {$cajaAbierta->fecha_cierre->format('d/m/Y H:i:s')}");
                $this->line("Monto Inicial: \${$cajaAbierta->monto_inicial}");
                $this->line("Total Sistema: \${$cajaAbierta->total_sistema}");
                $this->line("Monto Real: \${$cajaAbierta->monto_real}");
                $this->line("Diferencia: \${$cajaAbierta->diferencia}");
                $this->line('─────────────────────────────────');
                return 0;
            }

            // Abrir nueva caja
            $cajaExistente = Caja::where('usuario_id', $cajero->id)
                ->where('estado', 'abierta')
                ->first();

            if ($cajaExistente) {
                $this->error('Ya existe una caja abierta para este cajero');
                $this->line("Apertura: {$cajaExistente->fecha_apertura->format('d/m/Y H:i:s')}");
                return 1;
            }

            $caja = Caja::create([
                'usuario_id' => $cajero->id,
                'sucursal_id' => $cajero->branch_id,
                'monto_inicial' => 500,
                'fecha_apertura' => now(),
                'estado' => 'abierta',
            ]);

            $this->info('✓ Caja abierta correctamente');
            $this->line('');
            $this->line('📊 DETALLES DE APERTURA:');
            $this->line('─────────────────────────────────');
            $this->line("Cajero: {$caja->usuario->name}");
            $this->line("Sucursal: {$caja->sucursal->name ?? 'Principal'}");
            $this->line("Fecha y Hora Apertura: {$caja->fecha_apertura->format('d/m/Y H:i:s')}");
            $this->line("Monto Inicial: \${$caja->monto_inicial}");
            $this->line('─────────────────────────────────');
            $this->line('');
            $this->info('Para cerrar la caja, ejecuta: php artisan prueba:caja --cerrar');

            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
