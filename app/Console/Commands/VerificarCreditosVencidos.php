<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;

class VerificarCreditosVencidos extends Command
{
    protected $signature = 'creditos:verificar';
    protected $description = 'Verifica créditos vencidos y actualiza el estado de los clientes.';

    public function handle(): int
    {
        $clientes = Cliente::where('saldo_actual', '>', 0)->get();
        $updated = 0;

        foreach ($clientes as $cliente) {
            $original = $cliente->estado_credito;
            $cliente->actualizarEstadoCredito();
            if ($cliente->wasChanged('estado_credito')) {
                $updated++;
            }
        }

        $this->info("Clientes analizados: {$clientes->count()}");
        $this->info("Estados actualizados: {$updated}");

        return Command::SUCCESS;
    }
}
