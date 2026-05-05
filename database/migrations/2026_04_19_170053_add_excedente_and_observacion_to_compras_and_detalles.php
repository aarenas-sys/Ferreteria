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
        Schema::table('compras', function (Blueprint $table) {
            $table->text('observacion_cierre')->nullable();
        });

        Schema::table('compra_detalles', function (Blueprint $table) {
            $table->integer('cantidad_excedente')->default(0)->after('cantidad_recibida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropColumn('observacion_cierre');
        });

        Schema::table('compra_detalles', function (Blueprint $table) {
            $table->dropColumn('cantidad_excedente');
        });
    }
};
