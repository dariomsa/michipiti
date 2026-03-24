<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("UPDATE productos SET origen = 'pauta' WHERE origen = 'produccion'");

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE productos MODIFY origen ENUM('propuesta', 'pauta', 'comercial', 'pendiente') NOT NULL DEFAULT 'comercial'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE productos SET origen = 'produccion' WHERE origen = 'pauta'");
        DB::statement("UPDATE productos SET origen = 'propuesta' WHERE origen = 'pendiente'");

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE productos MODIFY origen ENUM('propuesta', 'produccion', 'comercial') NOT NULL DEFAULT 'comercial'");
    }
};
