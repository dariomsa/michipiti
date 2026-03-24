<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audiovisual_ediciones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audiovisual_id')->unique()->constrained('audiovisuales')->cascadeOnDelete();
            $table->string('entrevistador', 255)->nullable();
            $table->string('entrevistado', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audiovisual_ediciones');
    }
};
