<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear tabla ventas con todos los campos necesarios
        if (!Schema::hasTable('ventas')) {
            Schema::create('ventas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
                $table->foreignId('sucursal_id')->constrained('branches')->onDelete('cascade');
                $table->foreignId('descuento_id')->nullable()->constrained('discounts')->nullOnDelete();
                $table->enum('tipo_venta', ['contado', 'credito']);
                $table->decimal('subtotal', 10, 2);
                $table->decimal('iva', 10, 2);
                $table->decimal('descuento', 10, 2);
                $table->decimal('total', 10, 2);
                $table->enum('estado', ['completada', 'pendiente_pago', 'cancelada']);
                $table->date('fecha_venta');
                $table->date('fecha_vencimiento')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
