<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MultiplataformaCatalogosSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCatalog('multiplataforma_equipos', [
            ['id' => 1, 'nombre' => 'Michimercio', 'slug' => 'michimercio', 'activo' => true, 'orden' => 1, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 2, 'nombre' => 'Actualidad', 'slug' => 'actualidad', 'activo' => true, 'orden' => 2, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 3, 'nombre' => 'Breaking News', 'slug' => 'breaking-news', 'activo' => true, 'orden' => 3, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 4, 'nombre' => 'Profundidad', 'slug' => 'profundidad', 'activo' => true, 'orden' => 4, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 5, 'nombre' => 'Deportes', 'slug' => 'deportes', 'activo' => true, 'orden' => 5, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 6, 'nombre' => 'Tendencias', 'slug' => 'tendencias', 'activo' => true, 'orden' => 6, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 7, 'nombre' => 'Redes Sociales', 'slug' => 'redes-sociales', 'activo' => true, 'orden' => 7, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 9, 'nombre' => 'Multimedia', 'slug' => 'multimedia', 'activo' => true, 'orden' => 9, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 10, 'nombre' => 'EC-M@N', 'slug' => 'ec-m-at-n', 'activo' => true, 'orden' => 10, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 11, 'nombre' => 'Videografía', 'slug' => 'videografia', 'activo' => true, 'orden' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 12, 'nombre' => 'Pódcast', 'slug' => 'podcast', 'activo' => true, 'orden' => 1, 'created_at' => null, 'updated_at' => null],
        ]);

        $this->seedCatalog('multiplataforma_plataformas', [
            ['id' => 2, 'nombre' => 'Web', 'slug' => 'web', 'activo' => true, 'orden' => 2, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 4, 'nombre' => 'TikTok', 'slug' => 'tiktok', 'activo' => true, 'orden' => 4, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 5, 'nombre' => 'Facebook', 'slug' => 'facebook', 'activo' => true, 'orden' => 5, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 6, 'nombre' => 'YouTube', 'slug' => 'youtube', 'activo' => true, 'orden' => 6, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 7, 'nombre' => 'Shorts', 'slug' => 'shorts', 'activo' => true, 'orden' => 7, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 8, 'nombre' => 'WhatsApp', 'slug' => 'whatsapp', 'activo' => true, 'orden' => 8, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 9, 'nombre' => 'Radio', 'slug' => 'radio', 'activo' => true, 'orden' => 9, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 10, 'nombre' => 'Podcast', 'slug' => 'podcast', 'activo' => true, 'orden' => 10, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
        ]);

        $this->seedCatalog('multiplataforma_prioridades', [
            ['id' => 2, 'nombre' => 'Ecuador'],
            ['id' => 3, 'nombre' => 'Urgente'],
            ['id' => 4, 'nombre' => 'Semanal'],
            ['id' => 5, 'nombre' => 'Mes'],
        ]);

        $this->seedCatalog('multiplataforma_tipos', [
            ['id' => 2, 'nombre' => 'Editorial', 'slug' => 'editorial', 'activo' => true, 'orden' => 2, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 3, 'nombre' => 'Comercial', 'slug' => 'comercial', 'activo' => true, 'orden' => 3, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
            ['id' => 4, 'nombre' => 'Radio', 'slug' => 'radio', 'activo' => true, 'orden' => 4, 'created_at' => '2026-06-08 20:07:20', 'updated_at' => '2026-06-08 20:07:20'],
        ]);

        $this->seedHorarioSlots();
    }

    /**
     * @param  list<array{id:int,nombre:string,slug?:string,activo?:bool,orden?:int,created_at?:string|null,updated_at?:string|null}>  $items
     */
    private function seedCatalog(string $table, array $items): void
    {
        $now = now();

        foreach ($items as $index => $item) {
            $row = [
                'nombre' => $item['nombre'],
                'slug' => $item['slug'] ?? Str::slug($item['nombre']),
                'activo' => $item['activo'] ?? true,
                'orden' => $item['orden'] ?? $item['id'] ?? ($index + 1),
                'created_at' => array_key_exists('created_at', $item) ? $item['created_at'] : $now,
                'updated_at' => array_key_exists('updated_at', $item) ? $item['updated_at'] : $now,
            ];

            DB::table($table)->updateOrInsert(['id' => $item['id']], $row);
        }
    }

    private function seedHorarioSlots(): void
    {
        $timestamp = '2026-06-08 20:07:20';
        $empresaIds = [2, 4, 1, 7, 3, 6, 5];
        $slotId = 1;
        $rows = [];

        foreach ($empresaIds as $empresaId) {
            for ($day = 0; $day <= 6; $day++) {
                for ($minutes = 6 * 60; $minutes <= 23 * 60; $minutes += 15) {
                    $rows[] = [
                        'id' => $slotId++,
                        'empresa_id' => $empresaId,
                        'dia_semana' => $day,
                        'hora' => CarbonImmutable::createFromTime(0, 0)->addMinutes($minutes)->format('H:i:s'),
                        'visible' => true,
                        'fuera_de_pauta' => false,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }
            }
        }

        if (DB::table('multiplataforma_horarios_slots')->doesntExist()) {
            foreach (array_chunk($rows, 1000) as $chunk) {
                DB::table('multiplataforma_horarios_slots')->insert($chunk);
            }

            return;
        }

        foreach ($rows as $row) {
            $values = $row;
            unset($values['id']);

            DB::table('multiplataforma_horarios_slots')->updateOrInsert(
                [
                    'empresa_id' => $row['empresa_id'],
                    'dia_semana' => $row['dia_semana'],
                    'hora' => $row['hora'],
                ],
                $values
            );
        }
    }
}
