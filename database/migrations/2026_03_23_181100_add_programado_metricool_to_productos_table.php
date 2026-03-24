<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('productos', 'programado_metricool')) {
            return;
        }

        Schema::table('productos', function (Blueprint $table): void {
            $table->boolean('programado_metricool')->default(false)->after('pauta_comercial');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('productos', 'programado_metricool')) {
            return;
        }

        Schema::table('productos', function (Blueprint $table): void {
            $table->dropColumn('programado_metricool');
        });
    }
};
