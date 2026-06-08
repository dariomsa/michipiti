<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mundial_horarios_slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora');
            $table->boolean('visible')->default(true);
            $table->boolean('fuera_de_pauta')->default(false);
            $table->timestamps();

            $table->unique(['empresa_id', 'dia_semana', 'hora'], 'mundial_horarios_slots_empresa_dia_hora_unique');
        });

        $now = now();
        $rows = [];

        foreach (DB::table('empresas')->pluck('id') as $empresaId) {
            for ($day = 0; $day <= 6; $day++) {
                for ($minutes = 6 * 60; $minutes <= 23 * 60; $minutes += 15) {
                    $rows[] = [
                        'empresa_id' => $empresaId,
                        'dia_semana' => $day,
                        'hora' => sprintf('%02d:%02d:00', intdiv($minutes, 60), $minutes % 60),
                        'visible' => true,
                        'fuera_de_pauta' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table('mundial_horarios_slots')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mundial_horarios_slots');
    }
};
