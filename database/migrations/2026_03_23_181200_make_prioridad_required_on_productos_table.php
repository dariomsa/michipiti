<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('productos')
            ->whereNull('prioridad')
            ->update(['prioridad' => 'Día']);

        Schema::table('productos', function (Blueprint $table): void {
            $table->string('prioridad', 20)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->string('prioridad', 20)->nullable()->change();
        });
    }
};
