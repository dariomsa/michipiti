<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fin_semana', function (Blueprint $table): void {
            $table->unsignedSmallInteger('anio')->default(2026)->after('mes');
        });
    }

    public function down(): void
    {
        Schema::table('fin_semana', function (Blueprint $table): void {
            $table->dropColumn('anio');
        });
    }
};
