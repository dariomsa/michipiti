<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audiovisuales', function (Blueprint $table): void {
            $table->foreignId('tipo_audiovisual_id')->nullable()->after('id')->constrained('tipo_audiovisuales')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('audiovisuales', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tipo_audiovisual_id');
        });
    }
};
