<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('productos', 'multiplataforma_id')) {
            Schema::table('productos', function (Blueprint $table): void {
                $table->foreignId('multiplataforma_id')
                    ->nullable()
                    ->after('mundial_id')
                    ->constrained('multiplataforma_productos')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('productos', 'multiplataforma_id')) {
            Schema::table('productos', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('multiplataforma_id');
            });
        }
    }
};
