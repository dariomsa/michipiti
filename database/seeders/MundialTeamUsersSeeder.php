<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MundialTeamUsersSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Bendito Fútbol',
                'email' => 'equipo1@demo.com',
                'children' => ['Carlos Rojas'],
            ],
            [
                'name' => 'Deportes + EE.UU. (sede)',
                'email' => 'equipo2@demo.com',
                'children' => ['Carlos Rojas', 'Alexis Sinchire'],
            ],
            [
                'name' => 'Deportes / Giovanny',
                'email' => 'equipo3@demo.com',
                'children' => ['Carlos Rojas', 'Giovanni Astudillo'],
            ],
            [
                'name' => 'Equipo Actualidad',
                'email' => 'equipo4@demo.com',
                'children' => ['Jorge Imbaquingo'],
            ],
            [
                'name' => 'Equipo Deportes',
                'email' => 'equipo5@demo.com',
                'children' => ['Carlos Rojas'],
            ],
            [
                'name' => 'Equipo EE.UU.',
                'email' => 'equipo6@demo.com',
                'children' => ['Alexis Sinchire'],
            ],
            [
                'name' => 'Equipo Michimercio',
                'email' => 'equipo7@demo.com',
                'children' => ['Juan Carlos Ocaña'],
            ],
            [
                'name' => 'Equipo Profundidad',
                'email' => 'equipo8@demo.com',
                'children' => ['Gabriela Quiroz'],
            ],
            [
                'name' => 'Equipo Tendencias',
                'email' => 'equipo9@demo.com',
                'children' => ['Carolina Castillo'],
            ],
            [
                'name' => 'Equipo michi-mundialista',
                'email' => 'equipo10@demo.com',
                'children' => ['Juan Carlos Ocaña'],
            ],
            [
                'name' => 'Kevin Puga / Doménica Jarrín',
                'email' => 'equipo11@demo.com',
                'children' => ['Kevin Puga', 'Doménica Jarrín'],
            ],
        ];

        foreach ($teams as $team) {
            $childIds = User::query()
                ->whereIn('name', $team['children'])
                ->orderBy('id')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $user = User::query()->updateOrCreate(
                ['email' => $team['email']],
                [
                    'name' => $team['name'],
                    'password' => Hash::make('password'),
                    'usuarios_hijos' => $childIds,
                ],
            );

            if (! $user->hasRole('periodista')) {
                $user->assignRole('periodista');
            }
        }
    }
}
