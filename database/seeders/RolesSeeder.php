<?php

namespace Database\Seeders;

use App\Support\RoleCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RoleCatalog::names() as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
