<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Models\User;
use Illuminate\Database\Seeder;

class CatalogoBaseSeeder extends Seeder
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

        $tipoProducto = TipoProducto::query()->firstOrCreate(
            [
                'empresa_id' => $empresa->id,
                'slug' => 'tipo_carrusel',
            ],
            [
                'nombre' => 'tipo_carrusel',
                'descripcion' => 'Tipo base para productos de formato carrusel.',
                'estado' => 'activo',
            ]
        );

        $usuarioBase = User::query()->firstOrCreate(
            ['email' => 'catalogo@example.com'],
            [
                'name' => 'Catalogo Base',
                'password' => 'password',
            ]
        );

        Producto::query()->firstOrCreate(
            [
                'tipo_producto_id' => $tipoProducto->id,
                'titulo' => 'Carrusel',
            ],
            [
                'user_id' => $usuarioBase->id,
                'copy' => 'Producto inicial asociado al tipo carrusel.',
                'seccion' => 'Comercial',
                'estado' => 'BORRADOR',
                'dificultad' => 'BASICO',
                'origen' => 'comercial',
            ]
        );
    }
}
