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
        $this->createCatalogTable('mundial_prioridades');
        $this->createCatalogTable('mundial_plataformas');
        $this->createCatalogTable('mundial_equipos');
        $this->createCatalogTable('mundial_tipos');

        $this->seedCatalog('mundial_prioridades', [
            'Todas',
            'Ecuador',
            'Inauguración',
            'CONMEBOL',
            'Decisiva',
            'Final',
            'Regular',
        ]);

        $this->seedCatalog('mundial_plataformas', [
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

        $this->seedCatalog('mundial_equipos', [
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

        $this->seedCatalog('mundial_tipos', [
            'Todos',
            'Editorial',
            'Comercial',
            'Radio',
        ]);

        Schema::table('mundial_productos', function (Blueprint $table): void {
            $table->foreignId('mundial_prioridad_id')->nullable()->after('tipo_producto_id')->constrained('mundial_prioridades')->nullOnDelete();
            $table->foreignId('mundial_plataforma_id')->nullable()->after('mundial_prioridad_id')->constrained('mundial_plataformas')->nullOnDelete();
            $table->json('mundial_plataformas_ids')->nullable()->after('mundial_plataforma_id');
            $table->foreignId('mundial_equipo_id')->nullable()->after('mundial_plataforma_id')->constrained('mundial_equipos')->nullOnDelete();
            $table->foreignId('mundial_tipo_id')->nullable()->after('mundial_equipo_id')->constrained('mundial_tipos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mundial_productos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('mundial_tipo_id');
            $table->dropConstrainedForeignId('mundial_equipo_id');
            $table->dropColumn('mundial_plataformas_ids');
            $table->dropConstrainedForeignId('mundial_plataforma_id');
            $table->dropConstrainedForeignId('mundial_prioridad_id');
        });

        Schema::dropIfExists('mundial_tipos');
        Schema::dropIfExists('mundial_equipos');
        Schema::dropIfExists('mundial_plataformas');
        Schema::dropIfExists('mundial_prioridades');
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
