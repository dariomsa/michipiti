<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendario_especial', function (Blueprint $table): void {
            $table->id();
            $table->date('fecha')->unique();
            $table->string('motivo', 150);
            $table->unsignedTinyInteger('tipo_feriado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_especial');
    }
};
