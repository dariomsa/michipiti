<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstructuraProductoTest extends TestCase
{
    use RefreshDatabase;

    public function test_empresa_tipo_producto_y_producto_se_relacionan_correctamente(): void
    {
        $empresa = Empresa::create([
            'nombre' => 'Empresa Demo',
            'slug' => 'empresa-demo',
        ]);

        $tipoProducto = TipoProducto::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Cursos',
            'slug' => 'cursos',
        ]);

        $user = User::factory()->create();

        $producto = Producto::create([
            'tipo_producto_id' => $tipoProducto->id,
            'user_id' => $user->id,
            'titulo' => 'Curso Laravel',
            'seccion' => 'Tecnología',
        ]);

        $empresa->load('tipoProductos.productos');

        $this->assertCount(1, $empresa->tipoProductos);
        $this->assertSame('Cursos', $empresa->tipoProductos->first()->nombre);
        $this->assertCount(1, $empresa->tipoProductos->first()->productos);
        $this->assertSame('Curso Laravel', $empresa->tipoProductos->first()->productos->first()->titulo);
        $this->assertTrue($producto->tipoProducto->empresa->is($empresa));
        $this->assertTrue($producto->user->is($user));
    }
}
