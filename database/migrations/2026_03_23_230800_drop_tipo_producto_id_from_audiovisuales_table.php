<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('audiovisuales', 'tipo_producto_id')) {
            return;
        }

        Schema::table('audiovisuales', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tipo_producto_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('audiovisuales', 'tipo_producto_id')) {
            return;
        }

        Schema::table('audiovisuales', function (Blueprint $table): void {
            $table->foreignId('tipo_producto_id')->nullable()->after('id')->constrained('tipo_productos')->nullOnDelete();
        });
    }
};
