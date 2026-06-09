<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('productos', 'mundial_id')) {
            Schema::table('productos', function (Blueprint $table): void {
                $table->foreignId('mundial_id')
                    ->nullable()
                    ->after('empresa_id')
                    ->constrained('mundial_productos')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('productos', 'mundial_id')) {
            Schema::table('productos', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('mundial_id');
            });
        }
    }
};
