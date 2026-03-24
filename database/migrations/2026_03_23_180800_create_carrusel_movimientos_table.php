<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrusel_movimientos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('carrusel_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 60);
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30)->nullable();
            $table->string('motivo', 600)->nullable();
            $table->longText('meta')->nullable();
            $table->timestamps();

            $table->index(['carrusel_id', 'accion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrusel_movimientos');
    }
};
