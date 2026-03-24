<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audiovisual_grabacion_ediciones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audiovisual_id')->unique()->constrained('audiovisuales')->cascadeOnDelete();
            $table->string('producto_digital', 120)->nullable();
            $table->string('entrevistador', 255)->nullable();
            $table->string('entrevistado', 255)->nullable();
            $table->string('contacto_cobertura', 255)->nullable();
            $table->time('horario_grabacion')->nullable();
            $table->string('ubicacion', 255)->nullable();
            $table->string('brief_path', 600)->nullable();
            $table->string('brief_original_name', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audiovisual_grabacion_ediciones');
    }
};
