<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vigilia', function (Blueprint $table): void {
            $table->dropUnique('vigilia_semana_unique');
            $table->unsignedSmallInteger('anio')->default(2026)->after('sorteo_fecha');
            $table->unique(['anio', 'semana'], 'vigilia_anio_semana_unique');
        });
    }

    public function down(): void
    {
        Schema::table('vigilia', function (Blueprint $table): void {
            $table->dropUnique('vigilia_anio_semana_unique');
            $table->dropColumn('anio');
            $table->unique('semana');
        });
    }
};
