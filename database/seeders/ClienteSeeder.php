<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = [
            [
                'primer_nombre' => 'Juan',
                'segundo_nombre' => 'Carlos',
                'primer_apellido' => 'Pérez',
                'segundo_apellido' => 'Gómez',
                'documento' => '12345678',
                'email' => 'juan.perez@email.com',
                'telefono' => '+57 300 123 4567',
                'direccion' => 'Calle 123 #45-67, Bogotá',
                'cupo_credito' => 500000.00,
                'saldo_actual' => 0.00,
                'fecha_vencimiento_credito' => now()->addMonths(6),
                'estado_credito' => 'activo',
            ],
            [
                'primer_nombre' => 'María',
                'primer_apellido' => 'Rodríguez',
                'documento' => '87654321',
                'email' => 'maria.rodriguez@email.com',
                'telefono' => '+57 301 987 6543',
                'direccion' => 'Carrera 89 #12-34, Medellín',
                'cupo_credito' => 750000.00,
                'saldo_actual' => 150000.00,
                'fecha_vencimiento_credito' => now()->addMonths(3),
                'estado_credito' => 'activo',
            ],
            [
                'primer_nombre' => 'Carlos',
                'primer_apellido' => 'López',
                'documento' => '11223344',
                'telefono' => '+57 302 555 1234',
                'direccion' => 'Avenida 68 #45-12, Cali',
                'cupo_credito' => 0.00,
                'saldo_actual' => 0.00,
                'estado_credito' => 'bloqueado',
            ],
            [
                'primer_nombre' => 'Ana',
                'segundo_nombre' => 'María',
                'primer_apellido' => 'Martínez',
                'documento' => '44332211',
                'email' => 'ana.martinez@email.com',
                'telefono' => '+57 303 777 8888',
                'direccion' => 'Transversal 45 #67-89, Barranquilla',
                'cupo_credito' => 300000.00,
                'saldo_actual' => 350000.00,
                'fecha_vencimiento_credito' => now()->subDays(30), // Vencido
                'estado_credito' => 'mora',
            ],
        ];

        foreach ($clientes as $clienteData) {
            $cliente = Cliente::create($clienteData);
            $cliente->actualizarEstadoCredito();
            $cliente->save();
        }
    }
}
