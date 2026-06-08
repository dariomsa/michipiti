<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('mundial_productos', 'mundial_plataformas_ids')) {
            Schema::table('mundial_productos', function (Blueprint $table): void {
                $table->json('mundial_plataformas_ids')
                    ->nullable()
                    ->after('mundial_plataforma_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mundial_productos', 'mundial_plataformas_ids')) {
            Schema::table('mundial_productos', function (Blueprint $table): void {
                $table->dropColumn('mundial_plataformas_ids');
            });
        }
    }
};
