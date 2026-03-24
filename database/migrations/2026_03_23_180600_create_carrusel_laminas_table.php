<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carrusel_laminas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('carrusel_id')->constrained('productos')->cascadeOnDelete();
            $table->unsignedInteger('orden')->default(1);
            $table->string('titulo', 200)->nullable();
            $table->string('descripcion', 600)->nullable();
            $table->string('archivo_path', 600)->nullable();
            $table->string('archivo_original', 255)->nullable();
            $table->string('archivo_mime', 120)->nullable();
            $table->unsignedBigInteger('archivo_size')->nullable();
            $table->string('url_externa', 600)->nullable();
            $table->boolean('sin_foto')->default(false);
            $table->string('motivo', 255)->nullable();
            $table->timestamps();

            $table->index(['carrusel_id', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrusel_laminas');
    }
};
