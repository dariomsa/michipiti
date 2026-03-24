<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\TipoAudiovisual;
use Illuminate\Database\Seeder;

class TipoAudiovisualSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->firstOrCreate(
            ['slug' => 'el_comercio'],
            [
                'nombre' => 'el_comercio',
                'estado' => 'activa',
            ]
        );

        foreach ([
            [
                'slug' => TipoAudiovisual::SLUG_EDICION,
                'nombre' => 'Edición',
                'descripcion' => 'Audiovisual de tipo edición.',
            ],
            [
                'slug' => TipoAudiovisual::SLUG_GRABACION,
                'nombre' => 'Grabación',
                'descripcion' => 'Audiovisual de tipo grabación.',
            ],
            [
                'slug' => TipoAudiovisual::SLUG_GRABACION_EDICION,
                'nombre' => 'Grabación y edición',
                'descripcion' => 'Audiovisual de tipo grabación y edición.',
            ],
        ] as $tipoAudiovisual) {
            TipoAudiovisual::query()->firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'slug' => $tipoAudiovisual['slug'],
                ],
                [
                    'nombre' => $tipoAudiovisual['nombre'],
                    'descripcion' => $tipoAudiovisual['descripcion'],
                    'estado' => 'activo',
                ]
            );
        }
    }
}
