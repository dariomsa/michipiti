<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fin_semana', function (Blueprint $table): void {
            $table->id();
            $table->string('mes', 30);
            $table->string('fines_de_semana', 80);
            $table->string('jefe_turno', 150)->nullable();
            $table->string('grupo', 20);
            $table->string('deportes', 150)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['mes', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fin_semana');
    }
};
