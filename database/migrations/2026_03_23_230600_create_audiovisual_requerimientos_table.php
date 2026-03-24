<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audiovisual_requerimientos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audiovisual_id')->constrained('audiovisuales')->cascadeOnDelete();
            $table->string('nombre', 80);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audiovisual_requerimientos');
    }
};
