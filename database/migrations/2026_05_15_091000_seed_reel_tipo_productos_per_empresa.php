<?php

use App\Models\TipoProducto;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $empresaIds = DB::table('empresas')->pluck('id');

        foreach ($empresaIds as $empresaId) {
            DB::table('tipo_productos')->upsert(
                [
                    [
                        'empresa_id' => $empresaId,
                        'nombre' => 'Carrusel',
                        'slug' => TipoProducto::SLUG_CARRUSEL,
                        'descripcion' => null,
                        'estado' => 'activo',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    [
                        'empresa_id' => $empresaId,
                        'nombre' => 'Reel',
                        'slug' => TipoProducto::SLUG_REEL,
                        'descripcion' => null,
                        'estado' => 'activo',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                ],
                ['empresa_id', 'slug'],
                ['nombre', 'descripcion', 'estado', 'updated_at'],
            );
        }
    }

    public function down(): void
    {
    }
};
