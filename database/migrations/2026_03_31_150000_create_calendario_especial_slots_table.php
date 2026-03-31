<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendario_especial_slots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('tipo_feriado');
            $table->time('hora');
            $table->boolean('visible')->default(false);
            $table->boolean('fuera_de_pauta')->default(false);
            $table->timestamps();

            $table->unique(['tipo_feriado', 'hora']);
            $table->index(['tipo_feriado', 'visible']);
        });

        $now = now();
        $allHours = [];

        for ($minutes = 6 * 60; $minutes <= 22 * 60; $minutes += 15) {
            $allHours[] = sprintf('%02d:%02d:00', intdiv($minutes, 60), $minutes % 60);
        }

        $feriado1Hours = [
            '09:30:00',
            '10:45:00',
            '12:00:00',
            '13:30:00',
            '15:00:00',
            '16:30:00',
            '18:00:00',
            '19:30:00',
            '21:00:00',
            '22:00:00',
        ];

        DB::table('calendario_especial_slots')->insert(
            collect($allHours)->map(function (string $hour) use ($feriado1Hours, $now): array {
                return [
                    'tipo_feriado' => 1,
                    'hora' => $hour,
                    'visible' => in_array($hour, $feriado1Hours, true),
                    'fuera_de_pauta' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_especial_slots');
    }
};
