<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('branches')->cascadeOnDelete();
            $table->decimal('monto_inicial', 15, 2);
            $table->decimal('total_sistema', 15, 2)->nullable();
            $table->decimal('monto_real', 15, 2)->nullable();
            $table->decimal('diferencia', 15, 2)->nullable();
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->timestamps();

            $table->index(['usuario_id', 'sucursal_id'], 'caja_usuario_sucursal_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};