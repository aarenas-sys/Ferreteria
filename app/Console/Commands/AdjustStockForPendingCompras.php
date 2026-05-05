<?php

namespace App\Console\Commands;

use App\Models\Compra;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AdjustStockForPendingCompras extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:adjust-stock-for-pending-compras';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ajusta el stock de productos para compras en estado parcial o pendiente_confirmacion que ya incrementaron stock incorrectamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando ajuste de stock para compras pendientes...');

        $compras = Compra::with('detalles.producto')
            ->whereIn('estado', ['parcial', 'pendiente_confirmacion'])
            ->get();

        $this->info("Encontradas {$compras->count()} compras para ajustar");

        DB::transaction(function () use ($compras) {
            foreach ($compras as $compra) {
                $this->info("Ajustando compra #{$compra->id}");

                foreach ($compra->detalles as $detalle) {
                    if ($detalle->cantidad_recibida > 0) {
                        // Decrementar el stock que se incrementó incorrectamente
                        $detalle->producto->decrement('stock', $detalle->cantidad_recibida);
                        $this->line("  Producto {$detalle->producto->nombre}: stock decrementado en {$detalle->cantidad_recibida}");
                    }
                }
            }
        });

        $this->info('Ajuste de stock completado exitosamente');
    }
}
