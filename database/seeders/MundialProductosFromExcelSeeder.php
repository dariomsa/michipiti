<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\MundialEquipo;
use App\Models\MundialPlataforma;
use App\Models\MundialPrioridad;
use App\Models\MundialProducto;
use App\Models\MundialTipo;
use App\Models\TipoProducto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

class MundialProductosFromExcelSeeder extends Seeder
{
    private const FILE = 'referencia_mundial/SEED_Mundial_Michipiti.xlsx';

    public function run(): void
    {
        $this->call(MundialTeamUsersSeeder::class);

        $empresa = Empresa::query()->where('slug', 'el_comercio')->firstOrFail();
        $tipoProducto = TipoProducto::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', TipoProducto::SLUG_CARRUSEL)
            ->firstOrFail();

        $prioridades = $this->catalogMap(MundialPrioridad::query()->get(['id', 'nombre']));
        $plataformas = $this->catalogMap(MundialPlataforma::query()->get(['id', 'nombre']));
        $equipos = $this->catalogMap(MundialEquipo::query()->get(['id', 'nombre']));
        $tipos = $this->catalogMap(MundialTipo::query()->get(['id', 'nombre']));
        $users = $this->catalogMap(User::query()->get(['id', 'name']), 'name');

        $rows = $this->readProductosRows(public_path(self::FILE));
        $now = now();
        $created = 0;
        $updated = 0;

        DB::transaction(function () use (
            $rows,
            $empresa,
            $tipoProducto,
            $prioridades,
            $plataformas,
            $equipos,
            $tipos,
            $users,
            $now,
            &$created,
            &$updated,
        ): void {
            foreach ($rows as $row) {
                $platformNames = collect(explode(',', $row['Plataforma(s)']))
                    ->map(fn (string $name): string => trim($name))
                    ->filter()
                    ->values();

                $platformIds = $platformNames
                    ->map(fn (string $name): int => $this->lookup($plataformas, $name, 'plataforma'))
                    ->unique()
                    ->values()
                    ->all();

                $attributes = [
                    'empresa_id' => $empresa->id,
                    'fecha' => $row['Fecha'],
                    'hora' => $row['Hora'],
                    'titulo' => $row['Título'],
                ];

                $values = [
                    'tipo_producto_id' => $tipoProducto->id,
                    'mundial_prioridad_id' => $this->lookup($prioridades, $row['Prioridad'], 'prioridad'),
                    'mundial_plataforma_id' => $platformIds[0] ?? null,
                    'mundial_plataformas_ids' => $platformIds,
                    'mundial_equipo_id' => $this->lookup($equipos, $row['Equipo'], 'equipo'),
                    'mundial_tipo_id' => $this->lookup($tipos, $row['Tipo'], 'tipo'),
                    'user_id' => $this->lookup($users, $row['Líder'], 'líder'),
                    'responsable2_id' => $this->lookup($users, $row['Responsable'], 'responsable'),
                    'manager_id' => $row['Edición'] !== '' ? $this->lookup($users, $row['Edición'], 'edición') : null,
                    'redes_sociales_ids' => [],
                    'orden_dia' => $this->orderDiaFromTime($row['Hora']),
                    'seccion' => $row['Equipo'],
                    'copy' => $row['Nota'] !== '' ? $row['Nota'] : null,
                    'referencia' => $row['Estado'] !== '' ? $row['Estado'] : 'Borrador',
                    'creditos' => $row['Auspiciante'] !== '' ? $row['Auspiciante'] : null,
                    'estado' => 'BORRADOR',
                    'prioridad' => $row['Prioridad'],
                    'dificultad' => 'BASICO',
                    'pauta_comercial' => $this->normalize($row['Tipo']) === $this->normalize('Comercial'),
                    'programado_metricool' => false,
                    'origen' => 'propuesta',
                    'updated_at' => $now,
                ];

                $producto = MundialProducto::query()->firstOrNew($attributes);

                if (! $producto->exists) {
                    $producto->created_at = $now;
                    $created++;
                } else {
                    $updated++;
                }

                $producto->fill($values);
                $producto->save();
            }
        });

        $this->command?->info("Productos Mundial importados desde Excel. Creados: {$created}. Actualizados: {$updated}.");
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $items
     * @return array<string, int>
     */
    private function catalogMap($items, string $field = 'nombre'): array
    {
        return $items
            ->mapWithKeys(fn ($item): array => [$this->normalize((string) $item->{$field}) => (int) $item->id])
            ->all();
    }

    private function lookup(array $map, string $value, string $label): int
    {
        $key = $this->normalize($value);

        if (! isset($map[$key])) {
            throw new RuntimeException("No se encontró {$label}: {$value}");
        }

        return $map[$key];
    }

    /**
     * @return list<array<string, string>>
     */
    private function readProductosRows(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("No existe el archivo {$path}");
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException("No se pudo abrir el archivo {$path}");
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $xml = simplexml_load_string((string) $zip->getFromName('xl/worksheets/sheet1.xml'));

        if (! $xml) {
            throw new RuntimeException('No se pudo leer la hoja Productos del Excel.');
        }

        $headers = [];
        $rows = [];

        foreach ($xml->sheetData->row as $sheetRow) {
            $rowNumber = (int) $sheetRow['r'];
            $values = [];

            foreach ($sheetRow->c as $cell) {
                $values[$this->columnNumber((string) $cell['r'])] = $this->cellValue($cell, $sharedStrings);
            }

            if ($rowNumber === 1) {
                $headers = $values;
                continue;
            }

            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = trim($values[$index] ?? '');
            }

            if (($row['Título'] ?? '') !== '') {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = simplexml_load_string((string) $zip->getFromName('xl/sharedStrings.xml'));
        $strings = [];

        if (! $xml) {
            return $strings;
        }

        foreach ($xml->si as $item) {
            $text = '';

            if (isset($item->t)) {
                $text = (string) $item->t;
            } else {
                foreach ($item->r as $run) {
                    $text .= (string) $run->t;
                }
            }

            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * @param  \SimpleXMLElement  $cell
     * @param  list<string>  $sharedStrings
     */
    private function cellValue($cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];
        $value = (string) $cell->v;

        if ($type === 's') {
            return $sharedStrings[(int) $value] ?? '';
        }

        if ($type === 'inlineStr') {
            return (string) $cell->is->t;
        }

        return $value;
    }

    private function columnNumber(string $reference): int
    {
        preg_match('/^[A-Z]+/', $reference, $matches);
        $column = $matches[0] ?? '';
        $number = 0;

        foreach (str_split($column) as $char) {
            $number = ($number * 26) + ord($char) - 64;
        }

        return $number;
    }

    private function orderDiaFromTime(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return ($hour * 100) + $minute;
    }

    private function normalize(string $value): string
    {
        $value = trim($value);
        $value = strtr($value, [
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'Ñ' => 'N',
            'ñ' => 'n',
        ]);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return strtolower($value);
    }
}
