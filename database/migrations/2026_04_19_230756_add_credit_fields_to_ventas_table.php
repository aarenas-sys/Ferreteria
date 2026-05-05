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
        Schema::table('ventas', function (Blueprint $table) {
            $table->date('fecha_vencimiento')->nullable()->after('total');
            $table->enum('estado_credito', ['pendiente', 'pagado', 'mora'])->default('pagado')->after('fecha_vencimiento');
            $table->decimal('saldo_pendiente', 12, 2)->default(0)->after('estado_credito');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['fecha_vencimiento', 'estado_credito', 'saldo_pendiente']);
        });
    }
};
