<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vigilia', function (Blueprint $table): void {
            $table->id();
            $table->date('sorteo_fecha')->nullable();
            $table->unsignedSmallInteger('semana');
            $table->string('grupo', 20);
            $table->json('personas')->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->unique('semana');
            $table->index('orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vigilia');
    }
};
