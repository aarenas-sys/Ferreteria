<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE creditos MODIFY estado ENUM('pendiente','pagado') NOT NULL DEFAULT 'pendiente';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE creditos MODIFY estado ENUM('pendiente') NOT NULL DEFAULT 'pendiente';");
    }
};
