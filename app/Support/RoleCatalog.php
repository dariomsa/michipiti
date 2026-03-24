<?php

namespace App\Support;

class RoleCatalog
{
    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return [
            'admin',
            'periodista',
            'editor',
            'disenador',
            'disenador_manager',
            'comercial',
            'comunity_manager',
            'director',
            'videografia',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return collect(self::names())
            ->mapWithKeys(fn (string $role): array => [
                $role => str($role)->replace('_', ' ')->title()->toString(),
            ])
            ->all();
    }
}
