<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrusel_lamina_archivos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lamina_id')->constrained('carrusel_laminas')->cascadeOnDelete();
            $table->unsignedInteger('orden')->default(1);
            $table->string('archivo_path', 600);
            $table->string('archivo_original', 255)->nullable();
            $table->string('archivo_mime', 120)->nullable();
            $table->unsignedBigInteger('archivo_size')->nullable();
            $table->timestamps();

            $table->index(['lamina_id', 'orden']);
        });

        DB::table('carrusel_laminas')
            ->select(['id', 'archivo_path', 'archivo_original', 'archivo_mime', 'archivo_size'])
            ->whereNotNull('archivo_path')
            ->orderBy('id')
            ->get()
            ->each(function (object $lamina): void {
                DB::table('carrusel_lamina_archivos')->insert([
                    'lamina_id' => $lamina->id,
                    'orden' => 1,
                    'archivo_path' => $lamina->archivo_path,
                    'archivo_original' => $lamina->archivo_original,
                    'archivo_mime' => $lamina->archivo_mime,
                    'archivo_size' => $lamina->archivo_size,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrusel_lamina_archivos');
    }
};
