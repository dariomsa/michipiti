<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('multiplataforma_movimientos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('multiplataforma_producto_id')->constrained('multiplataforma_productos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 60);
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30)->nullable();
            $table->string('motivo', 600)->nullable();
            $table->longText('meta')->nullable();
            $table->timestamps();

            $table->index(['multiplataforma_producto_id', 'accion'], 'multiplataforma_movimientos_producto_accion_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('multiplataforma_movimientos');
    }
};
