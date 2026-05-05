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
        if (!Schema::hasColumn('ventas', 'descuento_id')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->foreignId('descuento_id')
                    ->nullable()
                    ->constrained('discounts')
                    ->nullOnDelete()
                    ->after('sucursal_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['descuento_id']);
            $table->dropColumn('descuento_id');
        });
    }
};
