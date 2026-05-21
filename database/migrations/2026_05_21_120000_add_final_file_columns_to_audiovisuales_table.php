<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audiovisuales', function (Blueprint $table): void {
            $table->string('archivo_final_path', 600)->nullable()->after('canva_url');
            $table->string('archivo_final_original_name', 255)->nullable()->after('archivo_final_path');
            $table->string('archivo_final_mime', 120)->nullable()->after('archivo_final_original_name');
            $table->unsignedBigInteger('archivo_final_size')->nullable()->after('archivo_final_mime');
        });
    }

    public function down(): void
    {
        Schema::table('audiovisuales', function (Blueprint $table): void {
            $table->dropColumn([
                'archivo_final_path',
                'archivo_final_original_name',
                'archivo_final_mime',
                'archivo_final_size',
            ]);
        });
    }
};
