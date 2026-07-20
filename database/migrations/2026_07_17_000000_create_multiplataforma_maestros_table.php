<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->createCatalogTable('multiplataforma_prioridades');
        $this->createCatalogTable('multiplataforma_plataformas');
        $this->createCatalogTable('multiplataforma_equipos');
        $this->createCatalogTable('multiplataforma_tipos');

        $this->seedCatalog('multiplataforma_prioridades', [
            'Todas',
            'Ecuador',
            'Inauguración',
            'CONMEBOL',
            'Decisiva',
            'Final',
            'Regular',
        ]);

        $this->seedCatalog('multiplataforma_plataformas', [
            'Todas',
            'Web',
            'Instagram',
            'TikTok',
            'Facebook',
            'YouTube',
            'Shorts',
            'WhatsApp',
            'Radio',
            'Podcast',
        ]);

        $this->seedCatalog('multiplataforma_equipos', [
            'Todos',
            'Actualidad',
            'Breaking News',
            'Profundidad',
            'Deportes',
            'Tendencias',
            'Redes Sociales',
            'EE.UU.',
            'Multimedia',
            'EC-M@N',
        ]);

        $this->seedCatalog('multiplataforma_tipos', [
            'Todos',
            'Editorial',
            'Comercial',
            'Radio',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('multiplataforma_tipos');
        Schema::dropIfExists('multiplataforma_equipos');
        Schema::dropIfExists('multiplataforma_plataformas');
        Schema::dropIfExists('multiplataforma_prioridades');
    }

    private function createCatalogTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 120);
            $table->string('slug', 140)->unique();
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(1);
            $table->timestamps();
        });
    }

    /**
     * @param  list<string>  $items
     */
    private function seedCatalog(string $tableName, array $items): void
    {
        foreach ($items as $index => $nombre) {
            DB::table($tableName)->insert([
                'nombre' => $nombre,
                'slug' => Str::slug($nombre),
                'activo' => true,
                'orden' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
