<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fin_semana', function (Blueprint $table): void {
            $table->foreignId('jefe_turno_user_id')
                ->nullable()
                ->after('jefe_turno')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fin_semana', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('jefe_turno_user_id');
        });
    }
};
