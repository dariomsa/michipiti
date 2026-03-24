<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('secciones', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        DB::table('secciones')->insert([
            ['id' => 1, 'nombre' => 'Actualidad', 'activa' => true],
            ['id' => 2, 'nombre' => 'Tendencias', 'activa' => true],
            ['id' => 3, 'nombre' => 'Tecnología', 'activa' => true],
            ['id' => 4, 'nombre' => 'Deportes', 'activa' => true],
            ['id' => 5, 'nombre' => 'Michimercio', 'activa' => true],
            ['id' => 6, 'nombre' => 'Comercial', 'activa' => true],
            ['id' => 7, 'nombre' => 'Afull', 'activa' => true],
            ['id' => 8, 'nombre' => 'Good Game', 'activa' => true],
            ['id' => 9, 'nombre' => 'Bendito Fútbol', 'activa' => true],
            ['id' => 10, 'nombre' => 'Sr. del Sombrero', 'activa' => true],
            ['id' => 11, 'nombre' => 'Líderes', 'activa' => true],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secciones');
    }
};
