<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_slots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora');
            $table->boolean('visible')->default(false);
            $table->boolean('fuera_de_pauta')->default(false);
            $table->timestamps();

            $table->unique(['dia_semana', 'hora']);
        });

        $weekdaysHours = [
            '06:00', '07:00', '08:15', '09:30', '10:45', '11:30', '12:15', '13:30',
            '14:45', '15:30', '16:00', '17:15', '18:30', '19:45', '20:15', '21:00',
        ];

        $saturdayHours = [
            '09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:30', '20:30', '22:00',
        ];

        $sundayHours = [
            '09:30', '10:45', '12:00', '13:30', '15:00', '16:30', '18:00', '19:30', '21:00', '22:00',
        ];

        $scheduleByDay = [
            0 => $weekdaysHours,
            1 => $weekdaysHours,
            2 => $weekdaysHours,
            3 => $weekdaysHours,
            4 => $weekdaysHours,
            5 => $saturdayHours,
            6 => $sundayHours,
        ];

        $visibleHours = collect(array_merge(...array_values($scheduleByDay)))
            ->unique()
            ->values()
            ->all();

        $rows = [];
        $now = now();

        for ($day = 0; $day <= 6; $day++) {
            for ($minutes = 6 * 60; $minutes <= 22 * 60; $minutes += 15) {
                $hour = sprintf('%02d:%02d:00', intdiv($minutes, 60), $minutes % 60);
                $hourHm = substr($hour, 0, 5);
                $isVisible = in_array($hourHm, $visibleHours, true);
                $isAllowed = in_array($hourHm, $scheduleByDay[$day] ?? [], true);

                $rows[] = [
                    'dia_semana' => $day,
                    'hora' => $hour,
                    'visible' => $isVisible,
                    'fuera_de_pauta' => $isVisible && ! $isAllowed,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('horario_slots')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_slots');
    }
};
