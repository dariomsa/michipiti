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
        DB::statement("ALTER TABLE productos MODIFY origen ENUM('propuesta', 'pauta', 'comercial', 'pendiente') NOT NULL DEFAULT 'comercial'");
        DB::statement("UPDATE productos SET origen = 'pauta' WHERE origen = 'produccion'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE productos SET origen = 'produccion' WHERE origen = 'pauta'");
        DB::statement("UPDATE productos SET origen = 'propuesta' WHERE origen = 'pendiente'");
        DB::statement("ALTER TABLE productos MODIFY origen ENUM('propuesta', 'produccion', 'comercial') NOT NULL DEFAULT 'comercial'");
    }
};
