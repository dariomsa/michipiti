<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinSemanaSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            ['id' => 1, 'mes' => 'ENERO', 'fines_de_semana' => '1', 'jefe_turno' => 'Castillo Espinoza Carolina Denise', 'grupo' => 'E'],
            ['id' => 2, 'mes' => 'ENERO', 'fines_de_semana' => '2', 'jefe_turno' => 'Imbaquingo Cabrera Jorge Raúl', 'grupo' => 'A'],
            ['id' => 3, 'mes' => 'ENERO', 'fines_de_semana' => '3', 'jefe_turno' => 'Quiroz Yánez Gabriela Giannina', 'grupo' => 'B'],
            ['id' => 4, 'mes' => 'ENERO', 'fines_de_semana' => '4', 'jefe_turno' => 'Jumbo Roman Betty Del Rocío', 'grupo' => 'C'],
            ['id' => 5, 'mes' => 'ENERO', 'fines_de_semana' => '5', 'jefe_turno' => 'Astudillo Orellana Carlos Giovanny', 'grupo' => 'D'],
            ['id' => 6, 'mes' => 'FEBRERO', 'fines_de_semana' => '1', 'jefe_turno' => 'Castillo Espinoza Carolina Denise', 'grupo' => 'E'],
            ['id' => 7, 'mes' => 'FEBRERO', 'fines_de_semana' => '2', 'jefe_turno' => 'Imbaquingo Cabrera Jorge Raúl', 'grupo' => 'A'],
            ['id' => 8, 'mes' => 'FEBRERO', 'fines_de_semana' => '3', 'jefe_turno' => 'Quiroz Yánez Gabriela Giannina', 'grupo' => 'B'],
            ['id' => 9, 'mes' => 'FEBRERO', 'fines_de_semana' => '4', 'jefe_turno' => 'Jumbo Roman Betty Del Rocío', 'grupo' => 'C'],
            ['id' => 10, 'mes' => 'MARZO', 'fines_de_semana' => '1', 'jefe_turno' => 'Astudillo Orellana Carlos Giovanny', 'grupo' => 'D'],
            ['id' => 11, 'mes' => 'MARZO', 'fines_de_semana' => '2', 'jefe_turno' => 'Castillo Espinoza Carolina Denise', 'grupo' => 'E'],
            ['id' => 12, 'mes' => 'MARZO', 'fines_de_semana' => '3', 'jefe_turno' => 'Imbaquingo Cabrera Jorge Raúl', 'grupo' => 'A'],
            ['id' => 13, 'mes' => 'MARZO', 'fines_de_semana' => '4', 'jefe_turno' => 'Quiroz Yánez Gabriela Giannina', 'grupo' => 'B'],
            ['id' => 14, 'mes' => 'ABRIL', 'fines_de_semana' => '1', 'jefe_turno' => 'Jumbo Roman Betty Del Rocío', 'grupo' => 'C'],
            ['id' => 15, 'mes' => 'ABRIL', 'fines_de_semana' => '2', 'jefe_turno' => 'Astudillo Orellana Carlos Giovanny', 'grupo' => 'D'],
            ['id' => 16, 'mes' => 'ABRIL', 'fines_de_semana' => '3', 'jefe_turno' => 'Castillo Espinoza Carolina Denise', 'grupo' => 'E'],
            ['id' => 17, 'mes' => 'ABRIL', 'fines_de_semana' => '4', 'jefe_turno' => 'Imbaquingo Cabrera Jorge Raúl', 'grupo' => 'A'],
            ['id' => 18, 'mes' => 'MAYO', 'fines_de_semana' => '1', 'jefe_turno' => 'Quiroz Yánez Gabriela Giannina', 'grupo' => 'B'],
            ['id' => 19, 'mes' => 'MAYO', 'fines_de_semana' => '2', 'jefe_turno' => 'Jumbo Roman Betty Del Rocío', 'grupo' => 'C'],
            ['id' => 20, 'mes' => 'MAYO', 'fines_de_semana' => '3', 'jefe_turno' => 'Astudillo Orellana Carlos Giovanny', 'grupo' => 'D'],
            ['id' => 21, 'mes' => 'MAYO', 'fines_de_semana' => '4', 'jefe_turno' => 'Castillo Espinoza Carolina Denise', 'grupo' => 'E'],
            ['id' => 22, 'mes' => 'MAYO', 'fines_de_semana' => '5', 'jefe_turno' => 'Imbaquingo Cabrera Jorge Raúl', 'grupo' => 'A'],
            ['id' => 23, 'mes' => 'JUNIO', 'fines_de_semana' => '1', 'jefe_turno' => 'Quiroz Yánez Gabriela Giannina', 'grupo' => 'B'],
            ['id' => 24, 'mes' => 'JUNIO', 'fines_de_semana' => '2', 'jefe_turno' => 'Jumbo Roman Betty Del Rocío', 'grupo' => 'C'],
            ['id' => 25, 'mes' => 'JUNIO', 'fines_de_semana' => '3', 'jefe_turno' => 'Astudillo Orellana Carlos Giovanny', 'grupo' => 'D'],
            ['id' => 26, 'mes' => 'JUNIO', 'fines_de_semana' => '4', 'jefe_turno' => 'Castillo Espinoza Carolina Denise', 'grupo' => 'E'],
            ['id' => 27, 'mes' => 'JULIO', 'fines_de_semana' => '1', 'jefe_turno' => 'Imbaquingo Cabrera Jorge Raúl', 'grupo' => 'A'],
            ['id' => 28, 'mes' => 'JULIO', 'fines_de_semana' => '2', 'jefe_turno' => 'Quiroz Yánez Gabriela Giannina', 'grupo' => 'B'],
            ['id' => 29, 'mes' => 'JULIO', 'fines_de_semana' => '3', 'jefe_turno' => 'Jumbo Roman Betty Del Rocío', 'grupo' => 'C'],
            ['id' => 30, 'mes' => 'JULIO', 'fines_de_semana' => '4', 'jefe_turno' => 'Astudillo Orellana Carlos Giovanny', 'grupo' => 'D'],
            ['id' => 31, 'mes' => 'AGOSTO', 'fines_de_semana' => '1', 'jefe_turno' => 'Castillo Espinoza Carolina Denise', 'grupo' => 'E'],
            ['id' => 32, 'mes' => 'AGOSTO', 'fines_de_semana' => '2', 'jefe_turno' => 'Imbaquingo Cabrera Jorge Raúl', 'grupo' => 'A'],
            ['id' => 33, 'mes' => 'AGOSTO', 'fines_de_semana' => '3', 'jefe_turno' => 'Quiroz Yánez Gabriela Giannina', 'grupo' => 'B'],
            ['id' => 34, 'mes' => 'AGOSTO', 'fines_de_semana' => '4', 'jefe_turno' => 'Jumbo Roman Betty Del Rocío', 'grupo' => 'C'],
            ['id' => 35, 'mes' => 'AGOSTO', 'fines_de_semana' => '5', 'jefe_turno' => 'Astudillo Orellana Carlos Giovanny', 'grupo' => 'D'],
            ['id' => 36, 'mes' => 'SEPTIEMBRE', 'fines_de_semana' => '1', 'jefe_turno' => 'Castillo Espinoza Carolina Denise', 'grupo' => 'E'],
            ['id' => 37, 'mes' => 'SEPTIEMBRE', 'fines_de_semana' => '2', 'jefe_turno' => 'Imbaquingo Cabrera Jorge Raúl', 'grupo' => 'A'],
            ['id' => 38, 'mes' => 'SEPTIEMBRE', 'fines_de_semana' => '3', 'jefe_turno' => 'Quiroz Yánez Gabriela Giannina', 'grupo' => 'B'],
            ['id' => 39, 'mes' => 'SEPTIEMBRE', 'fines_de_semana' => '4', 'jefe_turno' => 'Jumbo Roman Betty Del Rocío', 'grupo' => 'C'],
            ['id' => 40, 'mes' => 'OCTUBRE', 'fines_de_semana' => '1', 'jefe_turno' => 'Astudillo Orellana Carlos Giovanny', 'grupo' => 'D'],
            ['id' => 41, 'mes' => 'OCTUBRE', 'fines_de_semana' => '2', 'jefe_turno' => 'Castillo Espinoza Carolina Denise', 'grupo' => 'E'],
            ['id' => 42, 'mes' => 'OCTUBRE', 'fines_de_semana' => '3', 'jefe_turno' => 'Imbaquingo Cabrera Jorge Raúl', 'grupo' => 'A'],
            ['id' => 43, 'mes' => 'OCTUBRE', 'fines_de_semana' => '4', 'jefe_turno' => 'Quiroz Yánez Gabriela Giannina', 'grupo' => 'B'],
            ['id' => 44, 'mes' => 'OCTUBRE', 'fines_de_semana' => '5', 'jefe_turno' => 'Jumbo Roman Betty Del Rocío', 'grupo' => 'C'],
            ['id' => 45, 'mes' => 'NOVIEMBRE', 'fines_de_semana' => '1', 'jefe_turno' => 'Astudillo Orellana Carlos Giovanny', 'grupo' => 'D'],
            ['id' => 46, 'mes' => 'NOVIEMBRE', 'fines_de_semana' => '2', 'jefe_turno' => 'Castillo Espinoza Carolina Denise', 'grupo' => 'E'],
            ['id' => 47, 'mes' => 'NOVIEMBRE', 'fines_de_semana' => '3', 'jefe_turno' => 'Imbaquingo Cabrera Jorge Raúl', 'grupo' => 'A'],
            ['id' => 48, 'mes' => 'NOVIEMBRE', 'fines_de_semana' => '4', 'jefe_turno' => 'Quiroz Yánez Gabriela Giannina', 'grupo' => 'B'],
            ['id' => 49, 'mes' => 'DICIEMBRE', 'fines_de_semana' => '1', 'jefe_turno' => 'Jumbo Roman Betty Del Rocío', 'grupo' => 'C'],
            ['id' => 50, 'mes' => 'DICIEMBRE', 'fines_de_semana' => '2', 'jefe_turno' => 'Astudillo Orellana Carlos Giovanny', 'grupo' => 'D'],
            ['id' => 51, 'mes' => 'DICIEMBRE', 'fines_de_semana' => '3', 'jefe_turno' => 'Castillo Espinoza Carolina Denise', 'grupo' => 'E'],
            ['id' => 52, 'mes' => 'DICIEMBRE', 'fines_de_semana' => '4', 'jefe_turno' => 'Imbaquingo Cabrera Jorge Raúl', 'grupo' => 'A'],
        ];

        $rows = array_map(function (array $row) use ($now): array {
            return $row + [
                'jefe_turno_user_id' => 1,
                'deportes_user_id' => 1,
                'orden' => $row['id'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $rows);

        DB::table('fin_semana')->upsert(
            $rows,
            ['id'],
            ['mes', 'fines_de_semana', 'jefe_turno', 'jefe_turno_user_id', 'grupo', 'deportes_user_id', 'orden', 'updated_at']
        );
    }
}
