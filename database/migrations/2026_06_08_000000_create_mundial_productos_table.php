<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mundial_productos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('tipo_producto_id')->constrained('tipo_productos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('responsable2_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('redes_sociales_ids')->nullable();
            $table->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('disenador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->string('titulo', 200);
            $table->date('fecha')->nullable()->index();
            $table->time('hora')->nullable();
            $table->unsignedInteger('orden_dia')->nullable();
            $table->string('seccion', 100)->nullable();
            $table->text('copy')->nullable();
            $table->string('referencia', 600)->nullable();
            $table->string('hashtags', 600)->nullable();
            $table->string('canva_url', 600)->nullable();
            $table->string('creditos', 600)->nullable();
            $table->string('estado', 30)->default('BORRADOR')->index();
            $table->string('prioridad', 20)->default('Día');
            $table->string('dificultad', 20)->default('BASICO');
            $table->boolean('pauta_comercial')->default(false);
            $table->boolean('programado_metricool')->default(false);
            $table->enum('origen', ['propuesta', 'pauta', 'comercial', 'pendiente'])->default('comercial');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mundial_productos');
    }
};
