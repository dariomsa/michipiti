<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendario_especial', function (Blueprint $table): void {
            $table->string('grupo', 20)->nullable()->after('tipo_feriado');
            $table->string('deportes', 150)->nullable()->after('grupo');
        });
    }

    public function down(): void
    {
        Schema::table('calendario_especial', function (Blueprint $table): void {
            $table->dropColumn(['grupo', 'deportes']);
        });
    }
};
