<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $empresaId = $this->resolveDefaultEmpresaId();

        $this->addEmpresaColumn('productos', after: 'id');
        $this->addEmpresaColumn('audiovisuales', after: 'id');
        $this->addEmpresaColumn('secciones', after: 'id');
        $this->addEmpresaColumn('horario_slots', after: 'id');
        $this->addEmpresaColumn('calendario_especial', after: 'id');
        $this->addEmpresaColumn('calendario_especial_slots', after: 'id');
        $this->addEmpresaColumn('carrusel_laminas', after: 'id');
        $this->addEmpresaColumn('carrusel_lamina_archivos', after: 'id');
        $this->addEmpresaColumn('carrusel_mensajes', after: 'id');
        $this->addEmpresaColumn('carrusel_movimientos', after: 'id');
        $this->addEmpresaColumn('audiovisual_ediciones', after: 'id');
        $this->addEmpresaColumn('audiovisual_grabaciones', after: 'id');
        $this->addEmpresaColumn('audiovisual_grabacion_ediciones', after: 'id');
        $this->addEmpresaColumn('audiovisual_mensajes', after: 'id');
        $this->addEmpresaColumn('audiovisual_movimientos', after: 'id');
        $this->addEmpresaColumn('audiovisual_redes_sociales', after: 'id');
        $this->addEmpresaColumn('audiovisual_requerimientos', after: 'id');
        $this->addEmpresaColumnIfExists('metricool_posts', after: 'id');
        $this->addEmpresaColumnIfExists('metricool_product_matches', after: 'id');

        DB::statement("
            UPDATE productos p
            LEFT JOIN tipo_productos tp ON tp.id = p.tipo_producto_id
            SET p.empresa_id = COALESCE(tp.empresa_id, {$empresaId})
            WHERE p.empresa_id IS NULL
        ");

        DB::statement("
            UPDATE audiovisuales a
            LEFT JOIN tipo_audiovisuales ta ON ta.id = a.tipo_audiovisual_id
            SET a.empresa_id = COALESCE(ta.empresa_id, {$empresaId})
            WHERE a.empresa_id IS NULL
        ");

        DB::statement("UPDATE secciones SET empresa_id = {$empresaId} WHERE empresa_id IS NULL");
        DB::statement("UPDATE horario_slots SET empresa_id = {$empresaId} WHERE empresa_id IS NULL");
        DB::statement("UPDATE calendario_especial SET empresa_id = {$empresaId} WHERE empresa_id IS NULL");
        DB::statement("UPDATE calendario_especial_slots SET empresa_id = {$empresaId} WHERE empresa_id IS NULL");

        $this->deduplicateByGroup('horario_slots', ['empresa_id', 'dia_semana', 'hora']);
        $this->deduplicateByGroup('calendario_especial', ['empresa_id', 'fecha']);
        $this->deduplicateByGroup('calendario_especial_slots', ['empresa_id', 'tipo_feriado', 'hora']);

        DB::statement("
            UPDATE carrusel_laminas cl
            INNER JOIN productos p ON p.id = cl.carrusel_id
            SET cl.empresa_id = p.empresa_id
            WHERE cl.empresa_id IS NULL
        ");

        DB::statement("
            UPDATE carrusel_lamina_archivos cla
            INNER JOIN carrusel_laminas cl ON cl.id = cla.lamina_id
            SET cla.empresa_id = cl.empresa_id
            WHERE cla.empresa_id IS NULL
        ");

        DB::statement("
            UPDATE carrusel_mensajes cm
            INNER JOIN productos p ON p.id = cm.carrusel_id
            SET cm.empresa_id = p.empresa_id
            WHERE cm.empresa_id IS NULL
        ");

        DB::statement("
            UPDATE carrusel_movimientos cm
            INNER JOIN productos p ON p.id = cm.carrusel_id
            SET cm.empresa_id = p.empresa_id
            WHERE cm.empresa_id IS NULL
        ");

        DB::statement("
            UPDATE audiovisual_ediciones ae
            INNER JOIN audiovisuales a ON a.id = ae.audiovisual_id
            SET ae.empresa_id = a.empresa_id
            WHERE ae.empresa_id IS NULL
        ");

        DB::statement("
            UPDATE audiovisual_grabaciones ag
            INNER JOIN audiovisuales a ON a.id = ag.audiovisual_id
            SET ag.empresa_id = a.empresa_id
            WHERE ag.empresa_id IS NULL
        ");

        DB::statement("
            UPDATE audiovisual_grabacion_ediciones age
            INNER JOIN audiovisuales a ON a.id = age.audiovisual_id
            SET age.empresa_id = a.empresa_id
            WHERE age.empresa_id IS NULL
        ");

        DB::statement("
            UPDATE audiovisual_mensajes am
            INNER JOIN audiovisuales a ON a.id = am.audiovisual_id
            SET am.empresa_id = a.empresa_id
            WHERE am.empresa_id IS NULL
        ");

        DB::statement("
            UPDATE audiovisual_movimientos am
            INNER JOIN audiovisuales a ON a.id = am.audiovisual_id
            SET am.empresa_id = a.empresa_id
            WHERE am.empresa_id IS NULL
        ");

        DB::statement("
            UPDATE audiovisual_redes_sociales ars
            INNER JOIN audiovisuales a ON a.id = ars.audiovisual_id
            SET ars.empresa_id = a.empresa_id
            WHERE ars.empresa_id IS NULL
        ");

        DB::statement("
            UPDATE audiovisual_requerimientos ar
            INNER JOIN audiovisuales a ON a.id = ar.audiovisual_id
            SET ar.empresa_id = a.empresa_id
            WHERE ar.empresa_id IS NULL
        ");

        if (Schema::hasTable('metricool_posts') && Schema::hasColumn('metricool_posts', 'empresa_id')) {
            DB::statement("UPDATE metricool_posts SET empresa_id = {$empresaId} WHERE empresa_id IS NULL");
        }

        if (Schema::hasTable('metricool_product_matches') && Schema::hasColumn('metricool_product_matches', 'empresa_id')) {
            DB::statement("
                UPDATE metricool_product_matches m
                INNER JOIN productos p ON p.id = m.producto_id
                SET m.empresa_id = p.empresa_id
                WHERE m.empresa_id IS NULL
            ");
        }

        $this->makeEmpresaColumnRequired('productos');
        $this->makeEmpresaColumnRequired('audiovisuales');
        $this->makeEmpresaColumnRequired('secciones');
        $this->makeEmpresaColumnRequired('horario_slots');
        $this->makeEmpresaColumnRequired('calendario_especial');
        $this->makeEmpresaColumnRequired('calendario_especial_slots');
        $this->makeEmpresaColumnRequired('carrusel_laminas');
        $this->makeEmpresaColumnRequired('carrusel_lamina_archivos');
        $this->makeEmpresaColumnRequired('carrusel_mensajes');
        $this->makeEmpresaColumnRequired('carrusel_movimientos');
        $this->makeEmpresaColumnRequired('audiovisual_ediciones');
        $this->makeEmpresaColumnRequired('audiovisual_grabaciones');
        $this->makeEmpresaColumnRequired('audiovisual_grabacion_ediciones');
        $this->makeEmpresaColumnRequired('audiovisual_mensajes');
        $this->makeEmpresaColumnRequired('audiovisual_movimientos');
        $this->makeEmpresaColumnRequired('audiovisual_redes_sociales');
        $this->makeEmpresaColumnRequired('audiovisual_requerimientos');
        $this->makeEmpresaColumnRequiredIfExists('metricool_posts');
        $this->makeEmpresaColumnRequiredIfExists('metricool_product_matches');

        if ($this->hasIndex('horario_slots', 'horario_slots_dia_semana_hora_unique')) {
            Schema::table('horario_slots', function (Blueprint $table): void {
                $table->dropUnique('horario_slots_dia_semana_hora_unique');
            });
        }

        if (! $this->hasIndex('horario_slots', 'horario_slots_empresa_dia_hora_unique')) {
            Schema::table('horario_slots', function (Blueprint $table): void {
                $table->unique(['empresa_id', 'dia_semana', 'hora'], 'horario_slots_empresa_dia_hora_unique');
            });
        }

        if ($this->hasIndex('calendario_especial', 'calendario_especial_fecha_unique')) {
            Schema::table('calendario_especial', function (Blueprint $table): void {
                $table->dropUnique('calendario_especial_fecha_unique');
            });
        }

        if (! $this->hasIndex('calendario_especial', 'calendario_especial_empresa_fecha_unique')) {
            Schema::table('calendario_especial', function (Blueprint $table): void {
                $table->unique(['empresa_id', 'fecha'], 'calendario_especial_empresa_fecha_unique');
            });
        }

        if (! $this->hasIndex('calendario_especial_slots', 'calendario_especial_slots_empresa_tipo_hora_unique')) {
            Schema::table('calendario_especial_slots', function (Blueprint $table): void {
                $table->unique(['empresa_id', 'tipo_feriado', 'hora'], 'calendario_especial_slots_empresa_tipo_hora_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->hasIndex('horario_slots', 'horario_slots_empresa_dia_hora_unique')) {
            Schema::table('horario_slots', function (Blueprint $table): void {
                $table->dropUnique('horario_slots_empresa_dia_hora_unique');
            });
        }

        if (! $this->hasIndex('horario_slots', 'horario_slots_dia_semana_hora_unique')) {
            Schema::table('horario_slots', function (Blueprint $table): void {
                $table->unique(['dia_semana', 'hora'], 'horario_slots_dia_semana_hora_unique');
            });
        }

        if ($this->hasIndex('calendario_especial', 'calendario_especial_empresa_fecha_unique')) {
            Schema::table('calendario_especial', function (Blueprint $table): void {
                $table->dropUnique('calendario_especial_empresa_fecha_unique');
            });
        }

        if (! $this->hasIndex('calendario_especial', 'calendario_especial_fecha_unique')) {
            Schema::table('calendario_especial', function (Blueprint $table): void {
                $table->unique(['fecha'], 'calendario_especial_fecha_unique');
            });
        }

        if ($this->hasIndex('calendario_especial_slots', 'calendario_especial_slots_empresa_tipo_hora_unique')) {
            Schema::table('calendario_especial_slots', function (Blueprint $table): void {
                $table->dropUnique('calendario_especial_slots_empresa_tipo_hora_unique');
            });
        }

        $this->dropEmpresaColumnIfExists('metricool_product_matches');
        $this->dropEmpresaColumnIfExists('metricool_posts');
        $this->dropEmpresaColumn('audiovisual_requerimientos');
        $this->dropEmpresaColumn('audiovisual_redes_sociales');
        $this->dropEmpresaColumn('audiovisual_movimientos');
        $this->dropEmpresaColumn('audiovisual_mensajes');
        $this->dropEmpresaColumn('audiovisual_grabacion_ediciones');
        $this->dropEmpresaColumn('audiovisual_grabaciones');
        $this->dropEmpresaColumn('audiovisual_ediciones');
        $this->dropEmpresaColumn('carrusel_movimientos');
        $this->dropEmpresaColumn('carrusel_mensajes');
        $this->dropEmpresaColumn('carrusel_lamina_archivos');
        $this->dropEmpresaColumn('carrusel_laminas');
        $this->dropEmpresaColumn('calendario_especial_slots');
        $this->dropEmpresaColumn('calendario_especial');
        $this->dropEmpresaColumn('horario_slots');
        $this->dropEmpresaColumn('secciones');
        $this->dropEmpresaColumn('audiovisuales');
        $this->dropEmpresaColumn('productos');
    }

    protected function resolveDefaultEmpresaId(): int
    {
        $empresaId = DB::table('empresas')->orderBy('id')->value('id');

        if ($empresaId) {
            return (int) $empresaId;
        }

        return (int) DB::table('empresas')->insertGetId([
            'nombre' => 'Principal',
            'slug' => 'principal',
            'estado' => 'activa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function addEmpresaColumn(string $table, string $after = 'id'): void
    {
        if (Schema::hasColumn($table, 'empresa_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($after): void {
            $blueprint->foreignId('empresa_id')->nullable()->after($after)->constrained('empresas');
        });
    }

    protected function addEmpresaColumnIfExists(string $table, string $after = 'id'): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $this->addEmpresaColumn($table, $after);
    }

    protected function makeEmpresaColumnRequired(string $table): void
    {
        if (! Schema::hasColumn($table, 'empresa_id')) {
            return;
        }

        DB::statement("ALTER TABLE {$table} MODIFY empresa_id BIGINT UNSIGNED NOT NULL");
    }

    protected function makeEmpresaColumnRequiredIfExists(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $this->makeEmpresaColumnRequired($table);
    }

    protected function dropEmpresaColumn(string $table): void
    {
        if (! Schema::hasColumn($table, 'empresa_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->dropConstrainedForeignId('empresa_id');
        });
    }

    protected function dropEmpresaColumnIfExists(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $this->dropEmpresaColumn($table);
    }

    /**
     * @param  list<string>  $columns
     */
    protected function deduplicateByGroup(string $table, array $columns): void
    {
        $groupBy = implode(', ', $columns);

        DB::statement("
            DELETE t1
            FROM {$table} t1
            INNER JOIN {$table} t2
                ON t1.id > t2.id
               AND ".collect($columns)->map(fn ($column) => "t1.{$column} <=> t2.{$column}")->implode(' AND ')."
        ");
    }

    protected function hasIndex(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
