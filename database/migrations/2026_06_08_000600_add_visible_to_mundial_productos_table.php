<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('mundial_productos', 'visible')) {
            Schema::table('mundial_productos', function (Blueprint $table): void {
                $table->boolean('visible')->default(true)->after('programado_metricool');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mundial_productos', 'visible')) {
            Schema::table('mundial_productos', function (Blueprint $table): void {
                $table->dropColumn('visible');
            });
        }
    }
};
